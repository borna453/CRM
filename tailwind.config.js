import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/Components/*.php',
        './app/Livewire/**/*.php',
        './app/Utils/**/*.php',
        "./node_modules/flowbite/**/*.js",
        "./resources/**/*.js",
        './vendor/archilex/filament-filter-sets/**/*.php',
        './vendor/awcodes/filament-badgeable-column/resources/**/*.blade.php',
        './vendor/tomatophp/filament-artisan/resources/**/*.blade.php',
        './vendor/ralphjsmit/laravel-filament-onboard/resources/**/*.blade.php',
        './vendor/guava/filament-knowledge-base/src/**/*.php',
        './vendor/guava/filament-knowledge-base/resources/**/*.blade.php',
    ],
    plugins:[
      require('flowbite/plugin')
    ],
    darkMode: 'class'
}
