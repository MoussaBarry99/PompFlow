<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverCourseController;
use App\Http\Middleware\EnsureRole;


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('accueil');
})->name('accueil');
Route::get('/dashboard', function () {
     return in_array(auth()->user()->role, ['super_admin', 'responsable'], true) ? 
     redirect('/admin') : redirect()->route('chauffeur.courses.index'); 
})->middleware('auth')->name('dashboard'); 

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', EnsureRole::class.':chauffeur,employe']) ->prefix('chauffeur') ->name('chauffeur.') 
->group(function () 
{ 
    Route::get('courses', [DriverCourseController::class, 'index'])->name('courses.index'); 
    Route::get('courses/{course}', [DriverCourseController::class, 'show'])->name('courses.show'); 
    Route::post('courses/{course}/start', [DriverCourseController::class, 'start'])->name('courses.start'); 
    Route::post('courses/{course}/deliver', [DriverCourseController::class, 'deliver'])->name('courses.deliver'); 
    Route::post('courses/{course}/expenses', [DriverCourseController::class, 'storeExpense'])->name('courses.expenses.store'); 
    Route::delete('courses/{course}/expenses/{expense}', [DriverCourseController::class, 'destroyExpense'])->name('courses.expenses.destroy'); 
});

require __DIR__.'/auth.php';
