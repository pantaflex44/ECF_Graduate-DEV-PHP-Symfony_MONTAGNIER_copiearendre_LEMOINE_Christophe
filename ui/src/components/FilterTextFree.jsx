import React, { useEffect, useState } from "react";

export default function FilterTextFree({ id, label, value = '', onChange = null, ...props }) {
    const [data, setData] = useState(value);

    useEffect(() => {
        if (data !== value) setData(value);
    }, []);

    useEffect(() => { if (onChange) onChange({ [id]: data.trim() }) }, [data]);

    return <div className="mb-4" {...props}>
        <label htmlFor={`filter-${id}`} className="form-label small text-truncate w-100 mb-0">{label}</label>
        <input
            type="text"
            className="form-control"
            name={id}
            id={`filter-${id}`}
            value={data}
            onChange={(d) => { if (data !== d.target.value) setData(d.target.value) }}
        />
    </div>;
}