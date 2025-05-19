_You are an expert in PHP, Laravel, PHPUnit, and Tailwind.

1. Coding Standards
   •	Use PHP v8.4 features.
   •	Use Laravel 12 features.
   •	Use SOLID principles.
   •	Follow pint.json coding rules.
   •	Run pint after modifying files.
   •	Create interfaces for all repositories and services.
   •	Avoid using else in conditions.
   •	Add comments only for exceptions, otherwise don't create comments unless specifically requested.

2. Project Structure & Architecture
   •	Delete .gitkeep when adding a file.
   •	Avoid DB::; use direct model methods (e.g., Model::where(), Model::find()).
   •	Use repositories to interact with a database.
   •	Don't use "Interface" suffix for interface names.
   •	Register interfaces in AppServiceProvider.
   •	When registering interfaces, use "Contract" as the suffix for aliases.
   •	Register interfaces and implementations using the class-based approach: `$this->app->bind(InterfaceContract::class, Implementation::class);`

2.1 Directory Conventions

app/Http/Controllers
•	No abstract/base controllers.
•	Avoid using compact ind Controllers.

app/Http/Requests
•	Use FormRequest for validation.
•	Name with Create, Update, Delete.

app/Models
•	Use fillable.
•	Use #[ObservedBy([ObserverClass::class])] attribute to register model observers.

1. Testing
   •	Use Laravel Sail to run all tests: `./vendor/bin/sail test` or `./vendor/bin/sail artisan test`.
   •	For specific test files: `./vendor/bin/sail test tests/path/to/TestFile.php`.
   •	Don’t remove tests without approval.
   •	All code must be tested.
   •	Generate a {Model}Factory with each model.
   •	Use laravel faker or fake() helper function instead of hardcoded values.
   •	When using assertDatabase, specify the table using Model::class.

2.1 Test Directory Structure
•	Console: tests/Feature/Console
•	Controllers: tests/Feature/Http
•	Actions: tests/Unit/Actions
•	Models: tests/Unit/Models
•	Jobs: tests/Unit/Jobs

1. Styling & UI
   •	Use Tailwind CSS.
   •	Keep UI minimal.

2. Task Completion Requirements
   •	Recompile assets after frontend changes.
   •	Follow all rules before marking tasks complete._
