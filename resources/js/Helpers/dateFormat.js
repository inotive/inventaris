function createLocalDate(year, month, day, hour = 0, minute = 0, second = 0) {
    return new Date(year, month - 1, day, hour, minute, second);
}

function fallbackValue(value, fallback) {
    if (value === null || value === undefined || value === "") {
        return fallback;
    }

    return String(value);
}

export function hasTimePart(value) {
    return typeof value === "string" && /\d{2}:\d{2}/.test(value);
}

export function parseDateValue(value) {
    if (value === null || value === undefined || value === "") {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    if (typeof value === "number") {
        const parsedFromNumber = new Date(value);
        return Number.isNaN(parsedFromNumber.getTime()) ? null : parsedFromNumber;
    }

    if (typeof value !== "string") {
        return null;
    }

    const trimmedValue = value.trim();

    if (!trimmedValue) {
        return null;
    }

    let match = trimmedValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (match) {
        return createLocalDate(Number(match[1]), Number(match[2]), Number(match[3]));
    }

    match = trimmedValue.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
    if (match) {
        return createLocalDate(
            Number(match[1]),
            Number(match[2]),
            Number(match[3]),
            Number(match[4]),
            Number(match[5]),
            Number(match[6] || 0),
        );
    }

    match = trimmedValue.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (match) {
        return createLocalDate(Number(match[3]), Number(match[2]), Number(match[1]));
    }

    match = trimmedValue.match(/^(\d{2})-(\d{2})-(\d{4})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
    if (match) {
        return createLocalDate(
            Number(match[3]),
            Number(match[2]),
            Number(match[1]),
            Number(match[4]),
            Number(match[5]),
            Number(match[6] || 0),
        );
    }

    const parsed = new Date(trimmedValue);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

export function formatIndonesianDate(value, fallback = "-") {
    const parsed = parseDateValue(value);

    if (!parsed) {
        return fallbackValue(value, fallback);
    }

    return parsed.toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
}

export function formatIndonesianDateTime(value, fallback = "-") {
    const parsed = parseDateValue(value);

    if (!parsed) {
        return fallbackValue(value, fallback);
    }

    const datePart = formatIndonesianDate(parsed, fallback);
    const timePart = parsed.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });

    return `${datePart}, ${timePart}`;
}