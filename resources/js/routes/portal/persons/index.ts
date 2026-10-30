import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/portal/persons',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::index
* @see app/Http/Controllers/Portal/Company/PersonController.php:28
* @route '/portal/persons'
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
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/portal/persons/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::create
* @see app/Http/Controllers/Portal/Company/PersonController.php:44
* @route '/portal/persons/create'
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
* @see \App\Http\Controllers\Portal\Company\PersonController::store
* @see app/Http/Controllers/Portal/Company/PersonController.php:51
* @route '/portal/persons'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/portal/persons',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::store
* @see app/Http/Controllers/Portal/Company/PersonController.php:51
* @route '/portal/persons'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::store
* @see app/Http/Controllers/Portal/Company/PersonController.php:51
* @route '/portal/persons'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::store
* @see app/Http/Controllers/Portal/Company/PersonController.php:51
* @route '/portal/persons'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::store
* @see app/Http/Controllers/Portal/Company/PersonController.php:51
* @route '/portal/persons'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
export const edit = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/portal/persons/{person}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
edit.url = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { person: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { person: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            person: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        person: typeof args.person === 'object'
        ? args.person.id
        : args.person,
    }

    return edit.definition.url
            .replace('{person}', parsedArgs.person.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
edit.get = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
edit.head = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
const editForm = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
editForm.get = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::edit
* @see app/Http/Controllers/Portal/Company/PersonController.php:61
* @route '/portal/persons/{person}/edit'
*/
editForm.head = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
export const update = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/portal/persons/{person}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
update.url = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { person: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { person: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            person: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        person: typeof args.person === 'object'
        ? args.person.id
        : args.person,
    }

    return update.definition.url
            .replace('{person}', parsedArgs.person.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
update.put = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
update.patch = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
const updateForm = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
updateForm.put = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PersonController::update
* @see app/Http/Controllers/Portal/Company/PersonController.php:66
* @route '/portal/persons/{person}'
*/
updateForm.patch = (args: { person: number | { id: number } } | [person: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const persons = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
}

export default persons