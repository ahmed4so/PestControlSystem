import ResetPasswordScreen from '@/components/auth/reset-password-screen';

export default function ResetPassword(props: { token: string; email: string }) {
    return <ResetPasswordScreen {...props} />;
}
