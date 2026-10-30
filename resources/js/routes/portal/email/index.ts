import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
export const confirm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(args, options),
    method: 'get',
})

confirm.definition = {
    methods: ["get","head"],
    url: '/portal/settings/email/confirm/{token}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
confirm.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return confirm.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
confirm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
confirm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirm.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
const confirmForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: confirm.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
confirmForm.get = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: confirm.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::confirm
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:43
* @route '/portal/settings/email/confirm/{token}'
*/
confirmForm.head = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: confirm.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

confirm.form = confirmForm

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/portal/settings/email',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::edit
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:22
* @route '/portal/settings/email'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::store
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:33
* @route '/portal/settings/email'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/settings/email',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::store
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:33
* @route '/portal/settings/email'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::store
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:33
* @route '/portal/settings/email'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::store
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:33
* @route '/portal/settings/email'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Settings\LoginEmailController::store
* @see app/Http/Controllers/Portal/Settings/LoginEmailController.php:33
* @route '/portal/settings/email'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const email = {
    confirm: Object.assign(confirm, confirm),
    edit: Object.assign(edit, edit),
    store: Object.assign(store, store),
}

export default email