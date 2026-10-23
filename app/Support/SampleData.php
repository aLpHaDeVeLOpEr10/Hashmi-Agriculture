<?php

namespace App\Support;

class SampleData
{
    public static function livestock(): array
    {
        return [
            ['tag' => 'HF-0142', 'type' => 'Cattle', 'breed' => 'Holstein Friesian × Sahiwal', 'sex' => 'Female', 'age' => '4 y 2 m', 'weight' => 512, 'shed' => 'Shed 2', 'milk' => 22.5, 'health' => 'Healthy', 'status' => 'Lactating'],
            ['tag' => 'SW-0087', 'type' => 'Cattle', 'breed' => 'Sahiwal', 'sex' => 'Female', 'age' => '5 y 8 m', 'weight' => 438, 'shed' => 'Shed 1', 'milk' => 11.8, 'health' => 'Healthy', 'status' => 'Lactating'],
            ['tag' => 'NR-0231', 'type' => 'Buffalo', 'breed' => 'Nili-Ravi', 'sex' => 'Female', 'age' => '6 y 1 m', 'weight' => 590, 'shed' => 'Shed 3', 'milk' => 13.2, 'health' => 'Healthy', 'status' => 'Lactating'],
            ['tag' => 'NR-0198', 'type' => 'Buffalo', 'breed' => 'Nili-Ravi', 'sex' => 'Female', 'age' => '7 y 4 m', 'weight' => 612, 'shed' => 'Shed 3', 'milk' => 0, 'health' => 'Healthy', 'status' => 'Pregnant'],
            ['tag' => 'HF-0156', 'type' => 'Cattle', 'breed' => 'Holstein Friesian × Sahiwal', 'sex' => 'Female', 'age' => '3 y 6 m', 'weight' => 478, 'shed' => 'Shed 2', 'milk' => 0, 'health' => 'Under Treatment', 'status' => 'Dry'],
            ['tag' => 'CH-0044', 'type' => 'Cattle', 'breed' => 'Cholistani', 'sex' => 'Male', 'age' => '2 y 9 m', 'weight' => 395, 'shed' => 'Fattening Pen', 'milk' => 0, 'health' => 'Healthy', 'status' => 'Active'],
            ['tag' => 'BT-0312', 'type' => 'Goat', 'breed' => 'Beetal', 'sex' => 'Female', 'age' => '2 y 3 m', 'weight' => 46, 'shed' => 'Goat House', 'milk' => 1.6, 'health' => 'Healthy', 'status' => 'Lactating'],
            ['tag' => 'BT-0327', 'type' => 'Goat', 'breed' => 'Beetal', 'sex' => 'Male', 'age' => '1 y 6 m', 'weight' => 58, 'shed' => 'Goat House', 'milk' => 0, 'health' => 'Under Observation', 'status' => 'Active'],
            ['tag' => 'KJ-0019', 'type' => 'Sheep', 'breed' => 'Kajli', 'sex' => 'Female', 'age' => '3 y 0 m', 'weight' => 52, 'shed' => 'Sheep Pen', 'milk' => 0, 'health' => 'Healthy', 'status' => 'Pregnant'],
            ['tag' => 'SW-0101', 'type' => 'Cattle', 'breed' => 'Sahiwal', 'sex' => 'Female', 'age' => '1 y 4 m', 'weight' => 210, 'shed' => 'Heifer Shed', 'milk' => 0, 'health' => 'Healthy', 'status' => 'Active'],
        ];
    }

    public static function inventory(): array
    {
        return [
            ['sku' => 'FD-001', 'name' => 'Maize silage', 'category' => 'Feed', 'stock' => 1375, 'unit' => 't', 'capacity' => 2400, 'reorder' => 300, 'location' => 'Bunkers 1–2', 'supplier' => 'Own production', 'updated' => '21 Oct'],
            ['sku' => 'FD-004', 'name' => 'Wheat straw (bhoosa)', 'category' => 'Feed', 'stock' => 86, 'unit' => 't', 'capacity' => 200, 'reorder' => 40, 'location' => 'Straw shed', 'supplier' => 'Own production', 'updated' => '19 Oct'],
            ['sku' => 'FD-007', 'name' => 'Cotton seed cake (khal)', 'category' => 'Feed', 'stock' => 3.8, 'unit' => 't', 'capacity' => 12, 'reorder' => 4, 'location' => 'Feed Store 1', 'supplier' => 'Okara Oil Mills', 'updated' => '21 Oct'],
            ['sku' => 'FD-009', 'name' => 'Dairy concentrate (vanda) 18% CP', 'category' => 'Feed', 'stock' => 6.4, 'unit' => 't', 'capacity' => 10, 'reorder' => 3, 'location' => 'Feed Store 1', 'supplier' => 'Punjab Feeds', 'updated' => '21 Oct'],
            ['sku' => 'FD-012', 'name' => 'Mineral mixture', 'category' => 'Feed', 'stock' => 0, 'unit' => 'kg', 'capacity' => 500, 'reorder' => 100, 'location' => 'Feed Store 1', 'supplier' => 'Vet Care Traders', 'updated' => '18 Oct'],
            ['sku' => 'SD-003', 'name' => 'Wheat seed Akbar-19', 'category' => 'Seed', 'stock' => 68, 'unit' => 'bags', 'capacity' => 120, 'reorder' => 30, 'location' => 'Seed Store', 'supplier' => 'Punjab Seed Corp.', 'updated' => '15 Oct'],
            ['sku' => 'SD-008', 'name' => 'Mustard seed Super Raya', 'category' => 'Seed', 'stock' => 44, 'unit' => 'kg', 'capacity' => 60, 'reorder' => 20, 'location' => 'Seed Store', 'supplier' => 'Kissan Agri Store', 'updated' => '12 Oct'],
            ['sku' => 'FT-001', 'name' => 'Urea (46% N)', 'category' => 'Fertilizer', 'stock' => 18, 'unit' => 'bags', 'capacity' => 150, 'reorder' => 25, 'location' => 'Fertilizer Store', 'supplier' => 'Engro Fertilizers', 'updated' => '22 Oct'],
            ['sku' => 'FT-002', 'name' => 'DAP (18-46-0)', 'category' => 'Fertilizer', 'stock' => 42, 'unit' => 'bags', 'capacity' => 120, 'reorder' => 30, 'location' => 'Fertilizer Store', 'supplier' => 'Fauji Fertilizer', 'updated' => '21 Oct'],
            ['sku' => 'CH-005', 'name' => 'Silage inoculant (L. buchneri)', 'category' => 'Chemicals', 'stock' => 4, 'unit' => 'kg', 'capacity' => 20, 'reorder' => 5, 'location' => 'Chemical Store', 'supplier' => 'Agri Biotech Pvt.', 'updated' => '23 Oct'],
            ['sku' => 'CH-011', 'name' => 'Glyphosate 490 SL', 'category' => 'Chemicals', 'stock' => 36, 'unit' => 'L', 'capacity' => 80, 'reorder' => 15, 'location' => 'Chemical Store', 'supplier' => 'Kissan Agri Store', 'updated' => '10 Oct'],
            ['sku' => 'FL-001', 'name' => 'High-speed diesel', 'category' => 'Fuel', 'stock' => 1850, 'unit' => 'L', 'capacity' => 4000, 'reorder' => 800, 'location' => 'Fuel tank', 'supplier' => 'PSO Renala', 'updated' => '22 Oct'],
        ];
    }

    public static function employees(): array
    {
        return [
            ['name' => 'Tariq Mehmood', 'role' => 'Farm Manager', 'department' => 'Operations', 'farm' => 'Hashmi Main Farm', 'phone' => '+92 300 412 7781', 'joined' => 'Mar 2015', 'salary' => 145000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Asif Javed', 'role' => 'Farm Manager', 'department' => 'Operations', 'farm' => 'Riverside Farm', 'phone' => '+92 301 668 2190', 'joined' => 'Jan 2018', 'salary' => 130000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Nadeem Abbas', 'role' => 'Dairy Manager', 'department' => 'Livestock', 'farm' => 'Green Valley Dairy', 'phone' => '+92 333 905 4412', 'joined' => 'Jun 2019', 'salary' => 120000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Dr. Sana Iqbal', 'role' => 'Veterinarian', 'department' => 'Livestock', 'farm' => 'All farms', 'phone' => '+92 321 447 0935', 'joined' => 'Sep 2021', 'salary' => 110000, 'type' => 'Contract', 'status' => 'Present'],
            ['name' => 'Bilal Akram', 'role' => 'Field Supervisor', 'department' => 'Operations', 'farm' => 'Canal View Estate', 'phone' => '+92 345 210 6678', 'joined' => 'Feb 2020', 'salary' => 85000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Kamran Yousaf', 'role' => 'Silage Supervisor', 'department' => 'Operations', 'farm' => 'Hashmi Main Farm', 'phone' => '+92 302 781 3304', 'joined' => 'Apr 2022', 'salary' => 75000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Ayesha Siddiqui', 'role' => 'Accountant', 'department' => 'Finance', 'farm' => 'Head Office', 'phone' => '+92 311 554 9021', 'joined' => 'Aug 2020', 'salary' => 90000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Shahid Hussain', 'role' => 'Irrigation Supervisor', 'department' => 'Operations', 'farm' => 'Hashmi Main Farm', 'phone' => '+92 306 129 8875', 'joined' => 'Nov 2016', 'salary' => 62000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Rashid Ali', 'role' => 'Tractor Operator', 'department' => 'Machinery', 'farm' => 'Hashmi Main Farm', 'phone' => '+92 300 993 1146', 'joined' => 'May 2017', 'salary' => 48000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Usman Ghani', 'role' => 'Storekeeper', 'department' => 'Inventory', 'farm' => 'Hashmi Main Farm', 'phone' => '+92 334 671 2258', 'joined' => 'Jul 2021', 'salary' => 45000, 'type' => 'Permanent', 'status' => 'Late'],
            ['name' => 'Muhammad Arif', 'role' => 'Herdsman', 'department' => 'Livestock', 'farm' => 'Green Valley Dairy', 'phone' => '+92 303 418 7720', 'joined' => 'Jan 2019', 'salary' => 38000, 'type' => 'Permanent', 'status' => 'Present'],
            ['name' => 'Zahid Iqbal', 'role' => 'Field Worker', 'department' => 'Operations', 'farm' => 'Canal View Estate', 'phone' => '+92 307 552 3391', 'joined' => 'Mar 2023', 'salary' => 34000, 'type' => 'Seasonal', 'status' => 'Present'],
            ['name' => 'Ghulam Rasool', 'role' => 'Field Worker', 'department' => 'Operations', 'farm' => 'Riverside Farm', 'phone' => '+92 308 201 6645', 'joined' => 'Oct 2018', 'salary' => 34000, 'type' => 'Permanent', 'status' => 'On Leave'],
            ['name' => 'Waqas Ahmed', 'role' => 'Driver', 'department' => 'Machinery', 'farm' => 'All farms', 'phone' => '+92 312 845 0039', 'joined' => 'Dec 2022', 'salary' => 40000, 'type' => 'Permanent', 'status' => 'Absent'],
        ];
    }
}
