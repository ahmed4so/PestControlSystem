import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:84
* @route '/portal/premises/{premises}/checklist/{item}'
*/
export const update = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/portal/premises/{premises}/checklist/{item}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:84
* @route '/portal/premises/{premises}/checklist/{item}'
*/
update.url = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            premises: args[0],
            item: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        premises: typeof args.premises === 'object'
        ? args.premises.id
        : args.premises,
        item: typeof args.item === 'object'
        ? args.item.id
        : args.item,
    }

    return update.definition.url
            .replace('{premises}', parsedArgs.premises.toString())
            .replace('{item}', parsedArgs.item.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:84
* @route '/portal/premises/{premises}/checklist/{item}'
*/
update.put = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:84
* @route '/portal/premises/{premises}/checklist/{item}'
*/
const updateForm = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Portal\Company\PremisesController::update
* @see app/Http/Controllers/Portal/Company/PremisesController.php:84
* @route '/portal/premises/{premises}/checklist/{item}'
*/
updateForm.put = (args: { premises: number | { id: number }, item: number | { id: number } } | [premises: number | { id: number }, item: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const checklist = {
    update: Object.assign(update, update),
}

export default checklist