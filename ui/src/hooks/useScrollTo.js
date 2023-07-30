import React, { useEffect, useRef, useState } from "react";

const useScrollTo = () => {
    const ref = useRef(null);
    const [shouldScrollTo, setShouldScrollTo] = useState(false);

    useEffect(() => {
        if (ref.current && shouldScrollTo) {
            ref.current?.scrollIntoView({ behavior: 'smooth' });
            setShouldScrollTo(false);
        }
    }, [shouldScrollTo]);

    return [ref, setShouldScrollTo];
};

export default useScrollTo;