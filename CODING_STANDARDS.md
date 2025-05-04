You are an expert in PHP, Laravel, PHPUnit, and Tailwind.

1. Coding Standards
   •	Use PHP v8.4 features.

2. Project Structure & Architecture
   •	Delete .gitkeep when adding a file.
   •	Avoid DB::; use Model::query() only.

2.1 Directory Conventions

app/Http/Controllers
•	No abstract/base controllers.

app/Http/Requests
•	Use FormRequest for validation.
•	Name with Create, Update, Delete.

app/Models
•	Use fillable.

1. Testing
   •	Use Pest PHP for all tests.
   •	Run composer lint after changes.
   •	Run composer test before finalizing.
   •	Don’t remove tests without approval.
   •	All code must be tested.
   •	Generate a {Model}Factory with each model.

3.1 Test Directory Structure
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
   •	Follow all rules before marking tasks complete.
