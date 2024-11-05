<?php

namespace App\Providers\Filament;

use App\Enums\OnboardingTypes;
use App\Enums\PrimaryColor;
use App\Enums\ViewOptions;
use App\Filament\Pages\EditProfile;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\EngagementOverview;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\UserLocale;
use App\Livewire\Login;
use App\Livewire\UpcomingAppointmentsCalendar;
use App\Models\Company;
use App\Models\FinancialGoal;
use App\Models\NotificationTemplate;
use App\Models\Onboarding;
use App\Models\User;
use App\Utils\BrandLogoHelper;
use App\Utils\Filament\FormFields\FinancialGoalFormHelper;
use App\Utils\OnboardingHelper;
use Archilex\AdvancedTables\Plugin\AdvancedTablesPlugin;
use Cloudmazing\FilamentEmailLog\FilamentEmailLogPlugin;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step as WizardStep;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Guava\FilamentKnowledgeBase\KnowledgeBasePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use RalphJSmit\Filament\Onboard\FilamentOnboard;
use RalphJSmit\Filament\Onboard\Http\Livewire\Wizard;
use RalphJSmit\Filament\Onboard\Http\Middleware\OnboardMiddleware;
use RalphJSmit\Filament\Onboard\Step;
use RalphJSmit\Filament\Onboard\Track;
use RalphJSmit\Filament\Onboard\Widgets\OnboardTrackWidget;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile(page: EditProfile::class, isSimple: false)
            ->login(Login::class)
            ->passwordReset()
            ->colors([
                'primary' => config('adminpanel.primary'),
                'secondary' => config('adminpanel.secondary'),
            ])
            ->favicon(fn () => BrandLogoHelper::favicon())
            ->brandLogo(fn () => BrandLogoHelper::brandLogo())
            ->darkModeBrandLogo(fn () => BrandLogoHelper::darkModeBrandLogo())
            ->brandLogoHeight('4rem')
            ->sidebarWidth(config('adminpanel.sidebarWidth'))
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                OnboardTrackWidget::class,
                UpcomingAppointmentsCalendar::class,
                EngagementOverview::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                UserLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                OnboardMiddleware::class
            ])
            ->plugins([
                KnowledgeBasePlugin::make()->openDocumentationInNewTab()->modalPreviews(),
                FilamentApexChartsPlugin::make(),
                AdvancedTablesPlugin::make()
                    ->resourceEnabled(false)
                    ->userViewsEnabled(false)
                    ->viewManagerActiveViewIndicator()
                    ->favoritesBarDefaultView(false)
                    ->favoritesBarDivider()
                    ->presetViewsManageable(false)
                    ->viewManagerInFavoritesBar(false)
                    ->viewManagerInTable(false)
                    ->viewManagerSlideOver(),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable()
                    ->timezone(config('app.timezone'))
                    ->locale(config('app.locale')),
                new FilamentEmailLogPlugin,
                FilamentOnboard::make()
                    ->prefix(__('portal.onboarding.onboard'))
                    ->addTrack(fn() => Track::make([
                        Step::make(name: __('portal.onboarding.title'), identifier: 'widget::amend-user')->description(__('portal.onboarding.description'))
                        ->wizard([
                            WizardStep::make(__('portal.onboarding.amend_user'))->statePath('userData')
                                ->schema(
                                    UserResource::getFormSchema(ViewOptions::ONBOARDING)
                                ),
                            WizardStep::make(__('portal.onboarding.amend_company'))->statePath('companyData')
                                ->schema(
                                    CompanyResource::getFormSchema(ViewOptions::ONBOARDING)
                                ),
                            WizardStep::make(__('portal.email_settings.email'))->statePath('emailSettings')
                                ->schema([
                                    TextInput::make('from_name')
                                        ->label(__('portal.email_settings.from_name'))
                                        ->placeholder(auth()->user()->company->name ?? ''),
                                    RichEditor::make('footer')
                                        ->label(__('portal.email_settings.footer'))
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->wizardFillFormUsing(function(){
                            return[
                                'userData' => [
                                    'first_name' => auth()->user()->first_name,
                                    'last_name' => auth()->user()->last_name,
                                    'email' => auth()->user()->email,
                                ],
                                'companyData' => [
                                    'name' => auth()->user()->company?->name,
                                    'email' => auth()->user()->email
                                ],
                                'emailSettings' => [
                                    'from_name' => auth()->user()->company?->name,
                                ]
                            ];
                        })
                        ->wizardSubmitFormUsing(function(array $state, Wizard $livewire){
                            /** @var User $actor */
                            $actor = auth()->user();

                            $actor->fill($state['userData']);

                            $autoLogin = $actor->isDirty('password');

                            $actor->save();

                            unset($state['companyData']['choose']);

                            if(auth()->user()->company){
                                auth()->user()->company->update([
                                    'name' => $state['companyData']['name'],
                                    'email' => $state['companyData']['email'],
                                    'coc_number' => $state['companyData']['coc_number'],
                                    'phone_number' => $state['companyData']['phone_number'],
                                    'address' => $state['companyData']['address'],
                                    'zip_code' => $state['companyData']['zip_code'],
                                    'city' => $state['companyData']['city'],
                                ]);
                            } else{
                                $company = Company::create($state['companyData']);
                                $company->update(['is_main' => true]);
                                $actor->update(['company_id' => $company->id]);
                            }

                            $tenant = tenant();

                            $tenant->update([
                                'email' => array_merge($tenant->email ?? [], [
                                    'footer' => $state['emailSettings']['footer'],
                                    'from_name' => $state['emailSettings']['from_name'] ?? $actor->company->name,
                                    'custom_server' => false,
                                ]),
                            ]);

                            if ($autoLogin) {
                                session()->forget('password_hash_' . auth()->getDefaultDriver());
                                Filament::auth()->login($actor);
                                session()->regenerate();
                            }

                            $livewire->redirectRoute('filament.admin.pages.dashboard');
                        })
                        ->wizardSubmitButton(__('portal.onboarding.complete_onboarding'))
                        ->cardWidth('3xl')->completeIf(function () {
                                return auth()->user()->company && !auth()->user()->company?->hasIncompleteDetails();
                        }),
                    ])->completeBeforeAccess())
                ->addTrack(fn() => Track::make([
                    Step::make(name: '', identifier: 'widget::add-user')
                        ->description(__('portal.onboarding.add_user_description'))
                        ->icon('heroicon-s-user')
                        ->skipStepActionColor('primary')
                        ->performStepActionLabel(__('portal.onboarding.add_user'))
                        ->url(route('filament.admin.resources.companies.create', ["onboard" . '.' . OnboardingTypes::ADD_USER->value => true]))
                        ->completeIf(function () {
                            return OnboardingHelper::completeStep(OnboardingTypes::ADD_USER, function () {
                                return User::where('created_at', '>', auth()->user()->created_at)->exists();
                            });
                        })
                        ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ADD_USER)),
                    Step::make(name: '', identifier: 'widget::add-financial-goal')
                        ->description(__('portal.onboarding.set_financial_goal_description'))
                        ->icon('heroicon-s-currency-euro')
                        ->skipStepActionColor('primary')
                        ->performStepActionLabel(__('portal.onboarding.set_financial_goal'))
                        ->performStepAction(fn(Action $action) =>
                            $action
                            ->label(__('portal.onboarding.set_financial_goal'))
                            ->form(FinancialGoalFormHelper::fields())
                            ->action(function ($data) {
                                $data['year'] = date('Y');
                                return FinancialGoal::create($data);
                            })
                            ->modalWidth('2xl'))
                        ->completeIf(function () {
                            return OnboardingHelper::completeStep(OnboardingTypes::ADD_FINANCIAL_GOAL, function () {
                                return FinancialGoal::exists();
                            });
                        })
                        ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ADD_FINANCIAL_GOAL)),
                    Step::make(name: '', identifier: 'widget::edit-notification-template')
                        ->description(__('portal.onboarding.edit_notification_template_description'))
                        ->icon('heroicon-s-bell')
                        ->skipStepActionColor('primary')
                        ->performStepActionLabel(__('portal.onboarding.edit_notification_template'))
                        ->url(route('filament.admin.resources.notification-templates.index', ["onboard" . '.' . OnboardingTypes::EDIT_NOTIFICATION_TEMPLATE->value => true]))
                        ->completeIf(function () {
                            return OnboardingHelper::completeStep(OnboardingTypes::EDIT_NOTIFICATION_TEMPLATE, function () {
                                return NotificationTemplate::where('tenant_id', tenant()->id)->exists();
                            });
                        })
                        ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::EDIT_NOTIFICATION_TEMPLATE)),

//                    Step::make(name: '', identifier: 'widget::add-opportunity')
//                        ->description(__('portal.onboarding.add_opportunity_description'))
//                        ->icon('heroicon-o-rectangle-stack')
//                        ->performStepActionLabel(__('portal.onboarding.add_opportunity'))
//                        ->url(route('filament.admin.resources.opportunities.index', ["onboard" . '.' .  OnboardingTypes::ADD_OPPORTUNITY->value => true]))
//                        ->completeIf(function () {
//                            return OnboardingHelper::completeStep(OnboardingTypes::ADD_OPPORTUNITY, function () {
//                                return Opportunity::exists();
//                            });
//                        })
//                        ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ADD_OPPORTUNITY)),
                ])->sequential(false))
//                    ->addTrack(fn() => Track::make([
//                        Step::make(name: '', identifier: 'widget::add-employee')
//                            ->description(__('portal.onboarding.add_employee_description'))
//                            ->icon('heroicon-s-user-plus')
//                            ->performStepActionLabel(__('portal.onboarding.add_employee'))
//                            ->url(route('filament.admin.resources.users.create', ['onboard' . '.' . OnboardingTypes::ADD_EMPLOYEE->value => true]))
//                            ->completeIf(function () {
//                                return OnboardingHelper::completeStep(OnboardingTypes::ADD_EMPLOYEE, function () {
//                                    return User::role(User::EMPLOYEE)->exists();
//                                });
//                            })
//                            ->skippable(fn () => Onboarding::markStepAsComplete(OnboardingTypes::ADD_EMPLOYEE)),
//
//                        Step::make(name: '', identifier: 'widget::assign-task')
//                            ->description(__('portal.onboarding.assign_task_description'))
//                            ->icon('heroicon-s-check-circle')
//                            ->performStepActionLabel(__('portal.onboarding.assign_task'))
//                            ->url(route('filament.admin.resources.tasks.index', ['onboard' . '.' . OnboardingTypes::ASSIGN_TASK->value => true]))
//                            ->completeIf(function () {
//                                return OnboardingHelper::completeStep(OnboardingTypes::ASSIGN_TASK, function () {
//                                    return Task::where('user_id', User::role(User::EMPLOYEE)->first()?->id)->exists();
//                                });
//                            })
//                            ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ASSIGN_TASK)),
//
//                        Step::make(name: '', identifier: 'widget::impersonate-user')
//                            ->description(__('portal.onboarding.impersonate_user_description'))
//                            ->icon('heroicon-s-users')
//                            ->performStepActionLabel(__('portal.onboarding.impersonate_user'))
//                            ->url(route('filament.admin.resources.users.index', ['onboard' . '.' . OnboardingTypes::IMPERSONATE_USER->value => true]))
//                            ->completeIf(function () {
//                                return OnboardingHelper::completeStep(OnboardingTypes::IMPERSONATE_USER, function () {
//                                    return session()->has('onboarding_impersonated');
//                                });
//                            })
//                            ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::IMPERSONATE_USER)),
//                    ])->sequential(true))
//                    ->addTrack(fn() => Track::make([
//                        Step::make(name: '', identifier: 'widget::add-appointment')
//                            ->description(__('portal.onboarding.add_appointment_description'))
//                            ->icon('heroicon-s-calendar')
//                            ->performStepActionLabel(__('portal.onboarding.add_appointment'))
//                            ->url(route('filament.admin.resources.appointments.index', ['onboard' . '.' . OnboardingTypes::ADD_APPOINTMENT->value => true]))
//                            ->completeIf(function () {
//                                return OnboardingHelper::completeStep(OnboardingTypes::ADD_APPOINTMENT, function () {
//                                    return Appointment::exists();
//                                });
//                            })
//                            ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ADD_APPOINTMENT)),
//
//                        Step::make(name: '', identifier: 'widget::add-report')
//                            ->description(__('portal.onboarding.add_report_description'))
//                            ->icon('heroicon-s-document')
//                            ->performStepActionLabel(__('portal.onboarding.add_report'))
//                            ->url(route('filament.admin.resources.reports.index', ['onboard' . '.' . OnboardingTypes::ADD_REPORT->value => true]))
//                            ->completeIf(function () {
//                                return OnboardingHelper::completeStep(OnboardingTypes::ADD_REPORT, function () {
//                                    return Report::exists();
//                                });
//                            })
//                            ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::ADD_REPORT)),
//                    ]))->addTrack(fn() => Track::make([
//                        Step::make(name: '', identifier: 'widget::edit-email-settings')
//                            ->description(__('portal.onboarding.edit_email_settings_description'))
//                            ->icon('heroicon-s-envelope')
//                            ->performStepActionLabel(__('portal.onboarding.edit_email_settings'))
//                            ->url(route('filament.admin.pages.manage-email-settings', ['onboard' . '.' . OnboardingTypes::EDIT_EMAIL_SETTINGS->value => true]))
//                            ->completeIf(function () {
//                                return Onboarding::tenantId()->where('step', OnboardingTypes::EDIT_EMAIL_SETTINGS->value)->first()?->is_complete;
//                            })
//                            ->skippable(fn() => Onboarding::markStepAsComplete(OnboardingTypes::EDIT_EMAIL_SETTINGS)),
//                ]))
            ])
            ->databaseNotifications()->databaseNotificationsPolling(null);
    }
}
