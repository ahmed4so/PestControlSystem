import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import loginDf2c2a from './login'
import password from './password'
import twoFactor from './two-factor'
import signup7ed930 from './signup'
import temporaryPasswordC41443 from './temporary-password'
import email from './email'
import profile from './profile'
import userPassword from './user-password'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::login
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route '/login'
*/
loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: login.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

login.form = loginForm

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::logout
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route '/logout'
*/
logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

logout.form = logoutForm

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
export const signup = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: signup.url(options),
    method: 'get',
})

signup.definition = {
    methods: ["get","head"],
    url: '/signup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
signup.url = (options?: RouteQueryOptions) => {
    return signup.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
signup.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: signup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
signup.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: signup.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
const signupForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: signup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
signupForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: signup.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\SignUpController::signup
* @see app/Http/Controllers/Portal/Auth/SignUpController.php:23
* @route '/signup'
*/
signupForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: signup.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

signup.form = signupForm

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
export const temporaryPassword = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: temporaryPassword.url(options),
    method: 'get',
})

temporaryPassword.definition = {
    methods: ["get","head"],
    url: '/temporary-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
temporaryPassword.url = (options?: RouteQueryOptions) => {
    return temporaryPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
temporaryPassword.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: temporaryPassword.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
temporaryPassword.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: temporaryPassword.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
const temporaryPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: temporaryPassword.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
temporaryPasswordForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: temporaryPassword.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Auth\TemporaryPasswordController::temporaryPassword
* @see app/Http/Controllers/Portal/Auth/TemporaryPasswordController.php:25
* @route '/temporary-password'
*/
temporaryPasswordForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: temporaryPassword.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

temporaryPassword.form = temporaryPasswordForm

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/portal',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\DashboardController::__invoke
* @see app/Http/Controllers/Portal/DashboardController.php:16
* @route '/portal'
*/
dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: dashboard.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

dashboard.form = dashboardForm

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head","post","put","patch","delete","options"],
    url: '/portal/settings',
} satisfies RouteDefinition<["get","head","post","put","patch","delete","options"]>

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: settings.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: settings.url(options),
    method: 'put',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: settings.url(options),
    method: 'patch',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: settings.url(options),
    method: 'delete',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settings.options = (options?: RouteQueryOptions): RouteDefinition<'options'> => ({
    url: settings.url(options),
    method: 'options',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(options),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: settings.url(options),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: settings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: settings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: settings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \Illuminate\Routing\RedirectController::__invoke
* @see vendor/laravel/framework/src/Illuminate/Routing/RedirectController.php:19
* @route '/portal/settings'
*/
settingsForm.options = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'OPTIONS',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

settings.form = settingsForm

const portal = {
    login: Object.assign(login, loginDf2c2a),
    password: Object.assign(password, password),
    twoFactor: Object.assign(twoFactor, twoFactor),
    logout: Object.assign(logout, logout),
    signup: Object.assign(signup, signup7ed930),
    temporaryPassword: Object.assign(temporaryPassword, temporaryPasswordC41443),
    email: Object.assign(email, email),
    dashboard: Object.assign(dashboard, dashboard),
    settings: Object.assign(settings, settings),
    profile: Object.assign(profile, profile),
    userPassword: Object.assign(userPassword, userPassword),
}

export default portal