import React, { createRef, useEffect } from "react";

export default function Alert({ title, message, type = "alert-danger" }) {
    const alertRef = createRef();

    useEffect(() => {
        alertRef.current.scrollIntoView({ block: "end", behavior: 'smooth' });
    }, []);

    return (
        <div ref={alertRef} className={`alert ${type}`.trim()} role="alert">
            <h6 className="alert-heading small text-break">{title}</h6>
            <div className="small m-0 text-break"><small><small><div dangerouslySetInnerHTML={{ __html: message }} /></small></small></div>
        </div>
    );
}