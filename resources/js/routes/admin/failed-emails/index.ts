import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/failed-emails',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\FailedEmailController::index
* @see app/Http/Controllers/Admin/FailedEmailController.php:29
* @route '/admin/failed-emails'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const failedEmails = {
    index: Object.assign(index, index),
}

export default failedEmails