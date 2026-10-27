export type Permission = {
    id: number;
    name: string;
};

export type Role = {
    id: number;
    name: string;
    permissions?: Permission[];
};
