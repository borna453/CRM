<div x-data="{
    greetings: @js(__('login')),
    getGreeting() {
        const timezone = window.determineTimezone();
        const hour = new Date().toLocaleTimeString('en-US', { hour: 'numeric', hour12: false, timeZone: timezone });
        const hourInt = parseInt(hour);

        if (hourInt < 12) {
            return this.greetings.goodmorning;
        } else if (hourInt < 18) {
            return this.greetings.goodafternoon;
        } else {
            return this.greetings.goodevening;
        }
    }
}">
    <span x-text="getGreeting()"></span>
</div>
