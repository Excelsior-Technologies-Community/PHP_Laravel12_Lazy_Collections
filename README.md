# PHP_Laravel12_Lazy_Collections



## Project Description

PHP_Laravel12_Lazy_Collections is a Laravel 12 demonstration project created to understand and implement Lazy Collections for efficient data handling.
The main goal of this project is to show how Laravel can process large datasets efficiently by loading records one at a time instead of loading all records into memory.

This project is designed especially for beginners and freshers to clearly understand the difference between normal collections and lazy collections using a simple and practical example.


## Project Objective

To understand what Lazy Collections are in Laravel

To demonstrate how cursor() processes records one by one

To reduce memory usage while working with large database records

To implement Lazy Collections without using seeders

To follow proper Laravel 12 project structure and coding standards


## Technologies Used

PHP 8+

Laravel 12

MySQL

Eloquent ORM




---




# Project Setup & Step-by-Step Explanation

---

## STEP 1: Create New Laravel 12 Project

### Run Command :

```
composer create-project laravel/laravel PHP_Laravel12_Lazy_Collections "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_Lazy_Collections

```

Make sure Laravel 12 is installed successfully.



## STEP 2: Database Configuration

### Open .env file and update database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_lazy_collection
DB_USERNAME=root
DB_PASSWORD=

```

### Create database:

```
laravel12_lazy_collection

```



## STEP 3: Create Models + Migrations 

### Run Command:

```
php artisan make:model UserRecord -m

```
Explaination:

Creates a model for database interaction and a migration for table creation.
This follows Laravel’s MVC architecture.



## STEP 4: CHANGE MIGRATION NAME (IMPORTANT)

### Go to:

```
database/migrations/

```

### Rename migration file:

Before:

```
2026_01_28_000000_create_user_records_table.php

```

After (custom name):

```
2026_01_28_000000_create_lazy_user_records_table.php

```

Explaination:

Renames the migration file for better clarity and customization.
This helps clearly identify the purpose of the migration.




## STEP 5: Edit Migrations and Models

### database/migrations/2026_01_28_000000_create_lazy_user_records_table.php

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('lazy_user_records', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lazy_user_records');
    }
};


```


### app/Models/UserRecord.php

```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRecord extends Model
{
    protected $table = 'lazy_user_records';

    protected $fillable = [
        'name',
        'email',
    ];
}



```
### Then run migrations:

```
php artisan migrate

```

Explaination:

Defines the database table structure in the migration file.
The model is updated to map with the custom table name.




## STEP 6: Insert Data (NO Seeder)

### Open phpMyAdmin → lazy_user_records table

Insert data manually or run SQL:

```

INSERT INTO lazy_user_records (name, email, created_at, updated_at) VALUES
('User One', 'user1@gmail.com', NOW(), NOW()),
('User Two', 'user2@gmail.com', NOW(), NOW()),
('User Three', 'user3@gmail.com', NOW(), NOW()),
('User Four', 'user4@gmail.com', NOW(), NOW());


```
Explaination:

Dummy data is inserted manually into the database table.
Seeders are intentionally avoided as per project requirement.




## STEP 7: Create Controller

### Run command:

```
php artisan make:controller LazyCollectionController

```


### app/Http/Controllers/LazyCollectionController.php

```

<?php

namespace App\Http\Controllers;

use App\Models\UserRecord;

class LazyCollectionController extends Controller
{
    public function index()
    {
        // cursor() = Lazy Collection
        $users = UserRecord::cursor();

        $response = [];

        foreach ($users as $user) {
            // Processing ONE record at a time
            $response[] = [
                'id'    => $user->id,
                'name'  => strtoupper($user->name),
                'email' => $user->email,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Lazy Collection Example',
            'data' => $response
        ]);
    }
}

```

Explaination:

Creates a controller to handle Lazy Collection logic.
Controllers manage the application’s business logic.




## STEP 8: Route Setup

### routes/web.php

```

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LazyCollectionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lazy-users', [LazyCollectionController::class, 'index']);


```

Explaination:

Defines a route to access Lazy Collection data via URL.
Routes connect browser requests to controller methods.



## STEP 9: Run Project

## Start server:

```
php artisan serve

```

### Open browser:

```
http://127.0.0.1:8000/lazy-users

```



## So you can see this type Output:


<img width="1919" height="956" alt="Screenshot 2026-01-28 110359" src="https://github.com/user-attachments/assets/eb58442b-d658-40dd-8206-446bf4a0e352" />



---


# Project Folder Structure:

```

PHP_Laravel12_Lazy_Collections
│
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   └── LazyCollectionController.php
│   │
│   ├── Models
│   │   └── UserRecord.php
│   │
│   └── Providers
│
├── bootstrap
│   └── app.php
│
├── config
│   ├── app.php
│   ├── database.php
│   └── ...
│
├── database
│   ├── migrations
│   │   └── 2026_01_28_000000_create_lazy_user_records_table.php
│   │
│   └── factories
│
├── public
│   └── index.php
│
├── resources
│   ├── views
│   │   └── welcome.blade.php
│
├── routes
│   ├── web.php
│   └── console.php
│
├── storage
│   ├── app
│   ├── framework
│   └── logs
│
├── tests
│   ├── Feature
│   └── Unit
│
├── vendor
│
├── .env
├── .env.example
├── artisan
├── composer.json
├── composer.lock
├── package.json
└── README.md
```

```
