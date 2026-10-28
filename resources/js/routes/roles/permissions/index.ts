import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\RoleController::assign
* @see app/Http/Controllers/RoleController.php:104
* @route '/roles/permissions'
*/
export const assign = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assign.url(options),
    method: 'post',
})

assign.definition = {
    methods: ["post"],
    url: '/roles/permissions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoleController::assign
* @see app/Http/Controllers/RoleController.php:104
* @route '/roles/permissions'
*/
assign.url = (options?: RouteQueryOptions) => {
    return assign.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::assign
* @see app/Http/Controllers/RoleController.php:104
* @route '/roles/permissions'
*/
assign.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assign.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoleController::assign
* @see app/Http/Controllers/RoleController.php:104
* @route '/roles/permissions'
*/
const assignForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assign.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RoleController::assign
* @see app/Http/Controllers/RoleController.php:104
* @route '/roles/permissions'
*/
assignForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assign.url(options),
    method: 'post',
})

assign.form = assignForm

const permissions = {
    assign: Object.assign(assign, assign),
}

export default permissions