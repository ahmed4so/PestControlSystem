import ForgotPasswordScreen from '@/components/auth/forgot-password-screen';
import type { Honeypot } from '@/components/auth/honeypot-fields';

export default function ForgotPassword(props: { status?: string; honeypot: Honeypot }) {
    return <ForgotPasswordScreen {...props} />;
}
