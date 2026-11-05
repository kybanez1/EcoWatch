<?php

namespace App\Http\Controllers\Cgso;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TruckController extends Controller
{
    private const MESSAGES = [
        'truck_code.regex' => 'Truck code must look like TRUCK-001.',
        'truck_code.unique' => 'That truck code is already used.',
        'collector_id.unique' => 'That collector is already assigned to another truck.',
    ];

    public function index()
    {
        // The next free truck code, for example TRUCK-006.
        $highestNumber = 0;
        foreach (Truck::all() as $truck) {
            $number = (int) preg_replace('/\D/', '', $truck->truck_code);
            if ($number > $highestNumber) {
                $highestNumber = $number;
            }
        }
        $nextCode = 'TRUCK-' . str_pad((string) ($highestNumber + 1), 3, '0', STR_PAD_LEFT);

        return view('cgso.trucks', [
            'trucks' => Truck::with('collector')->orderBy('truck_code')->get(),
            'collectors' => User::where('role', 'collector')->where('is_active', true)->orderBy('name')->get(),
            'nextCode' => $nextCode,
            'newTruck' => new Truck,
        ]);
    }

    private function rules($truckId = null)
    {
        return [
            'truck_code' => ['required', 'regex:/^TRUCK-\d{3}$/', Rule::unique('trucks', 'truck_code')->ignore($truckId)],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('trucks', 'plate_number')->ignore($truckId)],
            'capacity_tons' => 'required|numeric|min:0|max:99',
            'status' => 'required|in:' . implode(',', Truck::STATUSES),
            'collector_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'collector')->where('is_active', true), Rule::unique('trucks', 'collector_id')->ignore($truckId)],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), self::MESSAGES);

        // Give the new truck the first map color that no other truck uses.
        $usedColors = Truck::pluck('color')->all();
        $data['color'] = Truck::COLORS[Truck::count() % count(Truck::COLORS)];
        foreach (Truck::COLORS as $color) {
            if (! in_array($color, $usedColors)) {
                $data['color'] = $color;
                break;
            }
        }

        $truck = Truck::create($data);
        return back()->with('success', $truck->label . ' added.');
    }

    public function update(Request $request, Truck $truck)
    {
        $data = $request->validate($this->rules($truck->id), self::MESSAGES);
        $truck->update($data);
        return back()->with('success', $truck->label . ' updated.');
    }

    public function status(Request $request, Truck $truck)
    {
        $data = $request->validate(['status' => 'required|in:' . implode(',', Truck::STATUSES)]);
        $truck->update($data);
        if ($truck->status === 'Maintenance') {
            return back()->with('success', $truck->label . ' is now under maintenance. Its schedules show "Service interrupted" to the public and collection updates are disabled.');
        }
        return back()->with('success', $truck->label . ' is now ' . $truck->status . '.');
    }

    public function destroy(Truck $truck)
    {
        $label = $truck->label;
        $truck->delete();
        return back()->with('success', $label . ' deleted along with its routes and schedules.');
    }
}
