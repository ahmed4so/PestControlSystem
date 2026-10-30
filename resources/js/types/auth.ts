/**
 * The logged-in account of the current area, as shared by
 * HandleInertiaRequests (staff user or company account).
 */
export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    two_factor_enabled: boolean;
    has_company: boolean;
};

export type Auth = {
    user: User | null;
    permissions: string[];
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
