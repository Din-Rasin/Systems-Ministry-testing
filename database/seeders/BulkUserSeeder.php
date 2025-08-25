<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all departments and roles
        $departments = Department::all();
        $roles = Role::all();

        // If we don't have enough data, return early
        if ($departments->isEmpty() || $roles->isEmpty()) {
            echo "Not enough data to seed users. Please run DepartmentSeeder and RoleSeeder first.\n";
            return;
        }

        // Define role weights for realistic distribution
        $roleWeights = [
            'User' => 60,           // 60% regular users
            'Team Leader' => 15,    // 15% team leaders
            'HR Manager' => 5,      // 5% HR managers
            'CFO' => 2,             // 2% CFOs
            'CEO' => 1,             // 1% CEOs
            'Department Administrator' => 10, // 10% department admins
            'System Administrator' => 7,      // 7% system admins
        ];

        // Generate 50 users
        for ($i = 0; $i < 50; $i++) {
            // Generate realistic user data
            $firstName = $this->getRandomFirstName();
            $lastName = $this->getRandomLastName();
            $name = $firstName . ' ' . $lastName;
            $email = strtolower($firstName . '.' . $lastName . rand(1, 999) . '@example.com');

            // Check if user already exists
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Create user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'), // Default password for all users
                    'remember_token' => Str::random(10),
                ]);

                // Assign department (random)
                $department = $departments->random();

                // Assign role based on weights
                $roleName = $this->getWeightedRandomRole($roleWeights);
                $role = $roles->firstWhere('name', $roleName);

                if ($role) {
                    // Attach role to user with department
                    $user->roles()->attach($role, [
                        'department_id' => $department->id,
                        'is_active' => true,
                    ]);
                }
            }
        }

        echo "50 users have been successfully seeded.\n";
    }

    /**
     * Get a random first name
     */
    private function getRandomFirstName()
    {
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
            'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph',
            'Jessica', 'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Nancy',
            'Daniel', 'Lisa', 'Matthew', 'Betty', 'Anthony', 'Helen', 'Mark', 'Sandra',
            'Donald', 'Donna', 'Steven', 'Carol', 'Paul', 'Ruth', 'Andrew', 'Sharon',
            'Joshua', 'Michelle', 'Kenneth', 'Amanda', 'Kevin', 'Dorothy', 'Brian', 'Alice',
            'George', 'Deborah', 'Edward', 'Rebecca', 'Ronald', 'Laura', 'Timothy', 'Cynthia',
            'Jason', 'Kathleen', 'Jeffrey', 'Amy', 'Ryan', 'Shirley', 'Jacob', 'Angela',
            'Gary', 'Anna', 'Nicholas', 'Brenda', 'Eric', 'Emma', 'Jonathan', 'Pamela',
            'Stephen', 'Nicole', 'Larry', 'Samantha', 'Justin', 'Katherine', 'Scott', 'Christine',
            'Brandon', 'Debra', 'Benjamin', 'Rachel', 'Samuel', 'Catherine', 'Frank', 'Carolyn',
            'Gregory', 'Janet', 'Raymond', 'Maria', 'Patrick', 'Heather', 'Alexander', 'Diane',
            'Jack', 'Julie', 'Dennis', 'Joyce', 'Jerry', 'Victoria', 'Tyler', 'Kelly',
            'Aaron', 'Christina', 'Jose', 'Lauren', 'Adam', 'Joan', 'Nathan', 'Evelyn',
            'Henry', 'Olivia', 'Zachary', 'Judith', 'Douglas', 'Megan', 'Peter', 'Cheryl',
            'Kyle', 'Andrea', 'Carl', 'Hannah', 'Arthur', 'Sara', 'Gerald', 'Jackie',
            'Terry', 'Sophia', 'Sean', 'Ashley', 'Christian', 'Renee', 'Wayne', 'Denise',
            'Roy', 'Marilyn', 'Louis', 'Amber', 'Russell', 'Danielle', 'Randy', 'Brittany',
            'Vincent', 'Abigail', 'Philip', 'Jane', 'Bobby', 'Gloria', 'Dylan', 'Mildred',
            'Johnny', 'Madison', 'Phillip', 'Beverly', 'Craig', 'Charlotte', 'Mary', 'Janice'
        ];

        return $firstNames[array_rand($firstNames)];
    }

    /**
     * Get a random last name
     */
    private function getRandomLastName()
    {
        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
            'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill',
            'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell',
            'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz',
            'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales',
            'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper', 'Peterson',
            'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward',
            'Richardson', 'Watson', 'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray',
            'Mendoza', 'Ruiz', 'Hughes', 'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel',
            'Myers', 'Long', 'Ross', 'Foster', 'Jimenez', 'Powell', 'Jenkins', 'Perry',
            'Russell', 'Sullivan', 'Bell', 'Coleman', 'Butler', 'Henderson', 'Barnes', 'Gonzales',
            'Fisher', 'Vasquez', 'Simmons', 'Romero', 'Jordan', 'Patterson', 'Alexander', 'Hamilton',
            'Graham', 'Reynolds', 'Griffin', 'Wallace', 'Moreno', 'West', 'Cole', 'Hayes',
            'Bryant', 'Herrera', 'Gibson', 'Ellis', 'Tran', 'Medina', 'Aguilar', 'Stevens',
            'Murray', 'Ford', 'Castro', 'Marshall', 'Owens', 'Harrison', 'Fernandez', 'McDonald',
            'Woods', 'Washington', 'Kennedy', 'Wells', 'Vargas', 'Henry', 'Chen', 'Freeman',
            'Webb', 'Tucker', 'Guzman', 'Burns', 'Crawford', 'Olson', 'Simpson', 'Porter'
        ];

        return $lastNames[array_rand($lastNames)];
    }

    /**
     * Get a weighted random role based on defined weights
     */
    private function getWeightedRandomRole($weights)
    {
        $totalWeight = array_sum($weights);
        $randomNumber = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weights as $role => $weight) {
            $currentWeight += $weight;
            if ($randomNumber <= $currentWeight) {
                return $role;
            }
        }

        // Fallback to regular user if something goes wrong
        return 'User';
    }
}
