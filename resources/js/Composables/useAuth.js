import { usePage } from "@inertiajs/vue3";

export function useAuth() {
    const page = usePage();

    const user = page.props.auth?.user ?? null;
    const roles = page.props.auth?.roles ?? [];
    const permissions = page.props.auth?.permissions ?? [];

    const is = (role) => roles.includes(role);
    const can = (permission) => permissions.includes(permission);

    return { user, roles, permissions, is, can };
}
