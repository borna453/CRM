# KlantenConnect

## Setup

On a fresh setup you can run:

```
php artisan migrate:fresh --seeder=DatabaseSeeder
```

On an existing setup just migrate as usual.

This will create an owner user and two example tenants called `cloudmazing` & `test` with its admin user, normal user and super user.

* Owner user: `job@cloudmazing.nl` (`secret`)
* `cloudmazing` tenant super admin user: `superadmin@cloudmazing.nl`
* `cloudmazing` tenant admin user: `admin@cloudmazing.nl`
* `cloudmazing` tenant regular user: `user@cloudmazing.nl`
* `test` tenant super admin user: `test.superadmin@cloudmazing.nl`
* `test` tenant admin user: `test.admin@cloudmazing.nl`
* `test` tenant regular user: `test.user@cloudmazing.nl`

Configure the env variables `APP_CENTRAL_DOMAIN `, `APP_TEST_TENANT_DOMAIN` and `APP_TEST_2_TENANT_DOMAIN`.

## Filament Panels

### Owner Panel

This panel is accessible from the central domain: `APP_CENTRAL_DOMAIN` (for example: `http://cicrm.lan/owner`).

The owner panel is only accessible to the owner user to manage tenants.

### Tenant Panels (Superadmin, admin & user)

These panels are specific to the tenant. Only accessible from a tenant domain. For example, the `cloudmazing` tenant domain `APP_TEST_TENANT_DOMAIN` (`http://cloudmazing.circrm.lan/admin`)

> [!NOTE]
> Check the `.env.example` for an idea on central vs tenant domains, those value will be saved to the domains table (in case of needed edits).

## Development tips

When writing a command that queries models and runs logic on each model, you must run the logic in the context of the model's tenant. For example

```php
User::query()
    ->each(function (User $user) {
        // Avoid this, it doesn't have tenant context.
        $user->notify(new CustomNotification); // this is just an example, it applies to any other kind of logic.
        
        // Do this instead
        $user->tenant->run(function () use ($user) {
            $user->notify(new CustomNotification);
        });
    });
```

> [!TIP]
> Full tenancy documentation at: https://tenancyforlaravel.com/docs/v3/introduction
