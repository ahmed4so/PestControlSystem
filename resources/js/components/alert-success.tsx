import { Alert } from '@heroui/alert';

interface AlertSuccessProps {
    message?: string;
    children?: React.ReactNode;
}

export default function AlertSuccess({ message, children }: AlertSuccessProps) {
    return <Alert color="success">{message || children}</Alert>;
}

