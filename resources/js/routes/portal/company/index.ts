import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/portal/company/setup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::create
* @see app/Http/Controllers/Portal/Company/CompanyController.php:28
* @route '/portal/company/setup'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::store
* @see app/Http/Controllers/Portal/Company/CompanyController.php:37
* @route '/portal/company'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/company',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::store
* @see app/Http/Controllers/Portal/Company/CompanyController.php:37
* @route '/portal/company'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::store
* @see app/Http/Controllers/Portal/Company/CompanyController.php:37
* @route '/portal/company'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::store
* @see app/Http/Controllers/Portal/Company/CompanyController.php:37
* @route '/portal/company'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::store
* @see app/Http/Controllers/Portal/Company/CompanyController.php:37
* @route '/portal/company'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/portal/company',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::edit
* @see app/Http/Controllers/Portal/Company/CompanyController.php:47
* @route '/portal/company'
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
* @see \App\Http\Controllers\Portal\Company\CompanyController::update
* @see app/Http/Controllers/Portal/Company/CompanyController.php:65
* @route '/portal/company'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/portal/company',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::update
* @see app/Http/Controllers/Portal/Company/CompanyController.php:65
* @route '/portal/company'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::update
* @see app/Http/Controllers/Portal/Company/CompanyController.php:65
* @route '/portal/company'
*/
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::update
* @see app/Http/Controllers/Portal/Company/CompanyController.php:65
* @route '/portal/company'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\CompanyController::update
* @see app/Http/Controllers/Portal/Company/CompanyController.php:65
* @route '/portal/company'
*/
updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const company = {
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default company