<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportReview;
use App\Models\Schedule;
use App\Models\Truck;
use App\Models\TruckRoute;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Small test data that mirrors the Figma demo screens. Safe to run more than once. */
class DatabaseSeeder extends Seeder
{
    /** Creates the user if the email does not exist yet. */
    private function makeUser($email, $name, $role, $password, $barangay = null)
    {
        $barangayId = null;
        if ($barangay) {
            $barangayId = $barangay->id;
        }
        return User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'role' => $role, 'password' => $password, 'barangay_id' => $barangayId]
        );
    }

    public function run(): void
    {
        $barangays = [];
        $barangayNames = ['San Juan', 'Washington', 'Luna', 'Taft', 'Rizal', 'Bonifacio', 'Ipil', 'Alang-alang', 'Anomar', 'Lipata'];
        foreach ($barangayNames as $name) {
            $barangays[$name] = Barangay::firstOrCreate(['name' => $name]);
        }

        $admin = $this->makeUser('admin@ecowatch.com', 'City Admin', 'cgso', 'admin123');
        $maria = $this->makeUser('resident@ecowatch.com', 'Maria Clara', 'resident', 'resident123', $barangays['San Juan']);
        $roberto = $this->makeUser('official.sanjuan@ecowatch.com', 'Roberto Cruz', 'official', 'official123', $barangays['San Juan']);
        $collectors = [
            1 => $this->makeUser('collector@ecowatch.com', 'Juan Dela Cruz', 'collector', 'collector123'),
            2 => $this->makeUser('pedro@ecowatch.com', 'Pedro Santos', 'collector', 'collector123'),
            3 => $this->makeUser('mario@ecowatch.com', 'Mario Reyes', 'collector', 'collector123'),
            4 => $this->makeUser('ana@ecowatch.com', 'Ana Villanueva', 'collector', 'collector123'),
            5 => $this->makeUser('carlo@ecowatch.com', 'Carlo Ramos', 'collector', 'collector123'),
        ];

        if (Truck::count() > 0) {
            return; // trucks, routes and reports are already seeded
        }

        // ---- Trucks ----
        $truckInfo = [
            1 => ['ABC-1234', 10, 'On Route', '#2F9E44'],
            2 => ['DEF-5678', 8, 'On Route', '#1971C2'],
            3 => ['GHI-9012', 10, 'Available', '#E8590C'],
            4 => ['JKL-3456', 6, 'Maintenance', '#9C36B5'],
            5 => ['MNO-7890', 10, 'Available', '#E03131'],
        ];
        $trucks = [];
        foreach ($truckInfo as $number => $info) {
            $trucks[$number] = Truck::create([
                'truck_code' => 'TRUCK-00' . $number,
                'plate_number' => $info[0],
                'capacity_tons' => $info[1],
                'status' => $info[2],
                'color' => $info[3],
                'collector_id' => $collectors[$number]->id,
            ]);
        }

        // ---- Routes and their schedules (one exact pickup time each) ----
        // [truck, route name, barangay, path, streets, days, pickup time]
        $routes = [
            [1, 'Route A', 'San Juan', [[9.7915, 125.4890], [9.7898, 125.4905], [9.7885, 125.4918]], ['Washington Street', 'Rizal Street', 'Borromeo Street', 'Luna Street'], ['Mon', 'Wed', 'Fri'], '08:00'],
            [2, 'Route B', 'Washington', [[9.7925, 125.4960], [9.7910, 125.4945], [9.7895, 125.4935]], ['Burgos Street'], ['Tue', 'Thu'], '07:00'],
            [3, 'Route C', 'Luna', [[9.7870, 125.4985], [9.7860, 125.4970], [9.7850, 125.4955]], ['Luna Street'], ['Wed'], '08:00'],
            [4, 'Route D', 'Taft', [[9.7935, 125.4920], [9.7928, 125.4930], [9.7920, 125.4940]], ['Taft Avenue'], ['Thu'], '13:00'],
            [5, 'Route E', 'Rizal', [[9.7840, 125.4900], [9.7855, 125.4915], [9.7865, 125.4930]], ['Del Pilar Street'], ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], '07:00'],
        ];
        foreach ($routes as $row) {
            $truckNumber = $row[0];
            $route = TruckRoute::create([
                'truck_id' => $trucks[$truckNumber]->id,
                'barangay_id' => $barangays[$row[2]]->id,
                'name' => $row[1],
                'path' => $row[3],
            ]);
            foreach ($row[4] as $position => $streetName) {
                $route->areas()->create(['name' => $streetName, 'sort' => $position]);
            }
            $isActive = true;
            if ($truckNumber === 4) {
                $isActive = false; // Truck #04 is under maintenance
            }
            Schedule::create([
                'truck_id' => $trucks[$truckNumber]->id,
                'barangay_id' => $barangays[$row[2]]->id,
                'truck_route_id' => $route->id,
                'days' => $row[5],
                'pickup_time' => $row[6],
                'is_active' => $isActive,
            ]);
        }
        Schedule::create(['truck_id' => $trucks[1]->id, 'barangay_id' => $barangays['Anomar']->id, 'days' => ['Sat'], 'pickup_time' => '07:00', 'is_active' => true]);
        Schedule::create(['truck_id' => $trucks[3]->id, 'barangay_id' => $barangays['Ipil']->id, 'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], 'pickup_time' => '15:00', 'is_active' => true]);

        // ---- Announcements ----
        $announcements = [
            ['Schedule Change', 'Holiday Schedule Adjustment', 'Collection for Barangay San Juan on August 28 will move to August 29 due to a local holiday.', '2026-08-25'],
            ['Truck Maintenance', 'Truck #04 Under Maintenance', 'Truck #04 is temporarily under maintenance. Route D collection may be delayed this week.', '2026-08-22'],
            ['Cleanup Drive', 'Coastal Cleanup Drive - September 6', 'Join the community coastal cleanup drive at Lipata Port. Volunteers may register at the barangay hall.', '2026-08-18'],
        ];
        foreach ($announcements as $row) {
            Announcement::create(['category' => $row[0], 'title' => $row[1], 'body' => $row[2], 'published_on' => $row[3]]);
        }

        // ---- Sample reports, one for each stage ----
        // [category, barangay, reporter, location, description, lat, lng, status, date, truck number, collector number]
        $reports = [
            ['Overflowing Garbage', 'San Juan', $maria, 'Near Washington St. corner, San Juan', 'Garbage bin overflowing for 3 days, attracting flies.', 9.7905, 125.4905, 'For Verification', '2026-08-20', 1, 1],
            ['Illegal Dumping', 'Luna', $maria, 'Vacant lot beside Ipil St., Luna', 'Someone dumped construction waste on the vacant lot.', 9.7862, 125.4972, 'In Progress', '2026-08-24', 3, 3],
            ['Missed Collection', 'San Juan', $roberto, 'Rizal Street, San Juan', 'Truck did not pass on Wednesday.', 9.7893, 125.4912, 'Pending', '2026-08-27', null, null],
            ['Uncollected Garbage', 'Rizal', $maria, 'Del Pilar Street, Rizal', 'Sacks left on the roadside after collection.', 9.7850, 125.4918, 'Resolved', '2026-08-15', 5, 5],
        ];
        $statusMessages = [
            'Pending' => 'Report submitted.',
            'Assigned' => 'Assigned to a collector.',
            'In Progress' => 'Collector started the cleanup.',
            'For Verification' => 'Cleanup submitted. Waiting for the CGSO Administrator to verify.',
            'Resolved' => 'Cleanup approved by the CGSO Administrator.',
        ];
        $statusOrder = ['Pending', 'Assigned', 'In Progress', 'For Verification', 'Resolved'];

        foreach ($reports as $index => $row) {
            $status = $row[7];
            $truckId = null;
            if ($row[9]) {
                $truckId = $trucks[$row[9]]->id;
            }
            $collectorId = null;
            if ($row[10]) {
                $collectorId = $collectors[$row[10]]->id;
            }
            $hasPhotos = ($index === 0);

            $report = Report::create([
                'code' => 'RPT-' . (1001 + $index),
                'category' => $row[0],
                'barangay_id' => $barangays[$row[1]]->id,
                'user_id' => $row[2]->id,
                'location_text' => $row[3],
                'description' => $row[4],
                'lat' => $row[5],
                'lng' => $row[6],
                'status' => $status,
                'assigned_truck_id' => $truckId,
                'collector_id' => $collectorId,
                'assigned_at' => $collectorId ? $row[8] : null,
                'photo_path' => $hasPhotos ? 'img/sample-proof.png' : null,
                'before_photo' => $hasPhotos ? 'img/sample-before.jpg' : null,
                'after_photo' => $hasPhotos ? 'img/sample-after.jpg' : null,
                'submitted_at' => $hasPhotos ? $row[8] : null,
                'verified_by' => ($status === 'Resolved') ? $admin->id : null,
                'verified_at' => ($status === 'Resolved') ? $row[8] : null,
            ]);

            // Timeline: one line for every step up to the current status.
            foreach ($statusOrder as $step) {
                $report->logs()->create(['status' => $step, 'message' => $statusMessages[$step], 'logged_on' => $row[8]]);
                if ($step === $status) {
                    break;
                }
            }

            if ($collectorId) {
                ReportAssignment::create([
                    'report_id' => $report->id,
                    'collector_id' => $collectorId,
                    'truck_id' => $truckId,
                    'assigned_by' => $admin->id,
                ]);
            }
            if ($hasPhotos) {
                ReportReview::create([
                    'report_id' => $report->id,
                    'action' => 'Submitted',
                    'user_id' => $collectorId,
                    'before_photo' => 'img/sample-before.jpg',
                    'after_photo' => 'img/sample-after.jpg',
                ]);
            }
        }
    }
}
