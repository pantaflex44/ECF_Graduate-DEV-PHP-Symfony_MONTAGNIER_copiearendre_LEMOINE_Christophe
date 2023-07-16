import React, { useEffect, useState } from "react";

const useDeviceSize = () => {
    const [width, setWidth] = useState(window.innerWidth);
    const [height, setHeight] = useState(window.innerHeight);
    const [orientation, setOrientation] = useState(window.innerHeight >= window.innerWidth ? 'portrait' : 'landscape');

    const handleWindowSizeChange = () => {
        setWidth(document.documentElement.clientWidth);
        setHeight(document.documentElement.clientHeight);
        setOrientation(window.innerHeight >= window.innerWidth ? 'portrait' : 'landscape');
    }

    useEffect(() => {
        window.addEventListener('resize', handleWindowSizeChange);
        return () => {
            window.removeEventListener('resize', handleWindowSizeChange);
        }
    }, []);

    return { width, height, orientation };
}

export default useDeviceSize;