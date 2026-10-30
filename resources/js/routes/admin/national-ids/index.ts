import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/national-ids',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::show
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:34
* @route '/admin/national-ids'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::find
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:39
* @route '/admin/national-ids/search'
*/
export const find = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: find.url(options),
    method: 'post',
})

find.definition = {
    methods: ["post"],
    url: '/admin/national-ids/search',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::find
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:39
* @route '/admin/national-ids/search'
*/
find.url = (options?: RouteQueryOptions) => {
    return find.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::find
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:39
* @route '/admin/national-ids/search'
*/
find.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: find.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::find
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:39
* @route '/admin/national-ids/search'
*/
const findForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: find.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::find
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:39
* @route '/admin/national-ids/search'
*/
findForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: find.url(options),
    method: 'post',
})

find.form = findForm

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::release
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:60
* @route '/admin/national-ids/release'
*/
export const release = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: release.url(options),
    method: 'post',
})

release.definition = {
    methods: ["post"],
    url: '/admin/national-ids/release',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::release
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:60
* @route '/admin/national-ids/release'
*/
release.url = (options?: RouteQueryOptions) => {
    return release.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::release
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:60
* @route '/admin/national-ids/release'
*/
release.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: release.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::release
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:60
* @route '/admin/national-ids/release'
*/
const releaseForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: release.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\NationalIdReleaseController::release
* @see app/Http/Controllers/Admin/NationalIdReleaseController.php:60
* @route '/admin/national-ids/release'
*/
releaseForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: release.url(options),
    method: 'post',
})

release.form = releaseForm

const nationalIds = {
    show: Object.assign(show, show),
    find: Object.assign(find, find),
    release: Object.assign(release, release),
}

export default nationalIds