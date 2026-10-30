import { usePage } from '@inertiajs/react';
import * as adminRoutes from '@/routes/admin';
import * as adminLogin from '@/routes/admin/login';
import * as adminPassword from '@/routes/admin/password';
import * as adminPasswordConfirm from '@/routes/admin/password/confirm';
import * as adminProfile from '@/routes/admin/profile';
import * as adminTwoFactor from '@/routes/admin/two-factor';
import * as adminTwoFactorLogin from '@/routes/admin/two-factor/login';
import * as adminUserPassword from '@/routes/admin/user-password';
import * as portalRoutes from '@/routes/portal';
import * as portalLogin from '@/routes/portal/login';
import * as portalPassword from '@/routes/portal/password';
import * as portalPasswordConfirm from '@/routes/portal/password/confirm';
import * as portalProfile from '@/routes/portal/profile';
import * as portalTwoFactor from '@/routes/portal/two-factor';
import * as portalTwoFactorLogin from '@/routes/portal/two-factor/login';
import * as portalUserPassword from '@/routes/portal/user-password';

/**
 * The two portals (D-048). Shared screens (login, password, 2FA) use the
 * routes of the area the page belongs to.
 */
export type Area = 'admin' | 'company';

const routes = {
    admin: {
        home: adminRoutes.dashboard,
        login: adminRoutes.login,
        loginStore: adminLogin.store,
        logout: adminRoutes.logout,
        passwordRequest: adminPassword.request,
        passwordEmail: adminPassword.email,
        passwordUpdate: adminPassword.update,
        passwordConfirmStore: adminPasswordConfirm.store,
        twoFactorLoginStore: adminTwoFactorLogin.store,
        twoFactorShow: adminTwoFactor.show,
        twoFactorEnable: adminTwoFactor.enable,
        twoFactorConfirm: adminTwoFactor.confirm,
        twoFactorDisable: null,
        twoFactorQrCode: adminTwoFactor.qrCode,
        twoFactorSecretKey: adminTwoFactor.secretKey,
        twoFactorRecoveryCodes: adminTwoFactor.recoveryCodes,
        twoFactorRegenerateRecoveryCodes:
            adminTwoFactor.regenerateRecoveryCodes,
        profileEdit: adminProfile.edit,
        userPasswordEdit: adminUserPassword.edit,
        userPasswordUpdate: adminUserPassword.update,
    },
    company: {
        home: portalRoutes.dashboard,
        login: portalRoutes.login,
        loginStore: portalLogin.store,
        logout: portalRoutes.logout,
        passwordRequest: portalPassword.request,
        passwordEmail: portalPassword.email,
        passwordUpdate: portalPassword.update,
        passwordConfirmStore: portalPasswordConfirm.store,
        twoFactorLoginStore: portalTwoFactorLogin.store,
        twoFactorShow: portalTwoFactor.show,
        twoFactorEnable: portalTwoFactor.enable,
        twoFactorConfirm: portalTwoFactor.confirm,
        twoFactorDisable: portalTwoFactor.disable,
        twoFactorQrCode: portalTwoFactor.qrCode,
        twoFactorSecretKey: portalTwoFactor.secretKey,
        twoFactorRecoveryCodes: portalTwoFactor.recoveryCodes,
        twoFactorRegenerateRecoveryCodes:
            portalTwoFactor.regenerateRecoveryCodes,
        profileEdit: portalProfile.edit,
        userPasswordEdit: portalUserPassword.edit,
        userPasswordUpdate: portalUserPassword.update,
    },
};

export type AreaRoutes = (typeof routes)['admin'] | (typeof routes)['company'];

export function areaRoutes(area: Area): AreaRoutes {
    return routes[area];
}

/**
 * The current page's area and its routes.
 */
export function useArea(): { area: Area; routes: AreaRoutes } {
    const area = usePage().props.area === 'admin' ? 'admin' : 'company';

    return { area, routes: routes[area] };
}
