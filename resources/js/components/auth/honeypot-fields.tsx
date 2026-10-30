/**
 * Hidden honeypot and fill-time fields for public account forms (D-080).
 * People never see or fill them; bots that do are refused silently.
 */
export type Honeypot = {
    enabled: boolean;
    nameFieldName: string;
    validFromFieldName: string;
    encryptedValidFrom: string;
};

export default function HoneypotFields({ honeypot }: { honeypot: Honeypot }) {
    if (!honeypot.enabled) {
        return null;
    }

    return (
        <div aria-hidden="true" className="hidden">
            <input
                type="text"
                name={honeypot.nameFieldName}
                id={honeypot.nameFieldName}
                tabIndex={-1}
                autoComplete="off"
                defaultValue=""
            />
            <input
                type="hidden"
                name={honeypot.validFromFieldName}
                value={honeypot.encryptedValidFrom}
            />
        </div>
    );
}
