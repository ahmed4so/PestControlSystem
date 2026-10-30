import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Public\LocaleController::__invoke
* @see app/Http/Controllers/Public/LocaleController.php:20
* @route '/locale/{locale}'
*/
export const update = (args: { locale: string | number } | [locale: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/locale/{locale}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Public\LocaleController::__invoke
* @see app/Http/Controllers/Public/LocaleController.php:20
* @route '/locale/{locale}'
*/
update.url = (args: { locale: string | number } | [locale: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { locale: args }
    }

    if (Array.isArray(args)) {
        args = {
            locale: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        locale: args.locale,
    }

    return update.definition.url
            .replace('{locale}', parsedArgs.locale.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Public\LocaleController::__invoke
* @see app/Http/Controllers/Public/LocaleController.php:20
* @route '/locale/{locale}'
*/
update.post = (args: { locale: string | number } | [locale: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Public\LocaleController::__invoke
* @see app/Http/Controllers/Public/LocaleController.php:20
* @route '/locale/{locale}'
*/
const updateForm = (args: { locale: string | number } | [locale: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Public\LocaleController::__invoke
* @see app/Http/Controllers/Public/LocaleController.php:20
* @route '/locale/{locale}'
*/
updateForm.post = (args: { locale: string | number } | [locale: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, options),
    method: 'post',
})

update.form = updateForm

const locale = {
    update: Object.assign(update, update),
}

export default locale