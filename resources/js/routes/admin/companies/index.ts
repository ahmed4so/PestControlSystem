import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/companies',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::index
* @see app/Http/Controllers/Admin/CompanyController.php:37
* @route '/admin/companies'
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

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
export const show = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/companies/{company}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
show.url = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { company: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { company: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            company: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        company: typeof args.company === 'object'
        ? args.company.id
        : args.company,
    }

    return show.definition.url
            .replace('{company}', parsedArgs.company.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
show.get = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
show.head = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
const showForm = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
showForm.get = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Admin\CompanyController::show
* @see app/Http/Controllers/Admin/CompanyController.php:59
* @route '/admin/companies/{company}'
*/
showForm.head = (args: { company: number | { id: number } } | [company: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const companies = {
    index: Object.assign(index, index),
    show: Object.assign(show, show),
}

export default companies