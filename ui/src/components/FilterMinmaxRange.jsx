import React, { useEffect, useState } from "react";

export default function FilterMinmaxRange({ id, label, min = 0, max = 1, value = '', onChange = null, ...props }) {
    const [data, setData] = useState({ min, max });
    const [limits, setLimits] = useState({ min, max });

    useEffect(() => {
        setLimits({ min, max });
    }, [min, max]);

    useEffect(() => {
        let v = { min, max };
        if (value !== null || value === '') {
            const r = value.toString().split(';');
            if (r.length === 2 && r[0] !== undefined && r[1] !== undefined) {
                v = { min: r[0], max: r[1] };
            } else {
                v = { min, max };
            }
        } else {
            v = { min, max };
        }
        if (JSON.stringify(data) !== JSON.stringify(v)) {
            setData(v);
        }
    }, []);

    useEffect(() => {
        if (onChange) onChange({
            [id]: `${data.min};${data.max}`
        })
    }, [data]);

    return <div className="mb-4" {...props}>
        <label htmlFor={`filter-${id}`} className="form-label small text-truncate w-100 mb-0">{label}</label>
        <div className="row g-1">
            <div className="col">
                <div className="vstack">
                    <input
                        type="number"
                        className="form-control"
                        name={`${id}-min`}
                        id={`filter-${id}-min`}
                        value={data.min}
                        min={limits.min}
                        max={data.max}
                        step={1}
                        onChange={(d) => { if (data.min !== d.target.value) setData(old => ({ ...old, min: d.target.value })) }}
                        onBlur={() => {
                            let dmin = data.min;
                            if (dmin < limits.min) dmin = limits.min;
                            if (dmin > data.max) dmin = data.max;
                            setData(old => ({ ...old, min: dmin }));
                        }}
                    />
                    <span className="small text-secondary"><small>Minimum</small></span>
                </div>
            </div>
            <div className="col">
                <div className="vstack">
                    <input
                        type="number"
                        className="form-control"
                        name={`${id}-max`}
                        id={`filter-${id}-max`}
                        value={data.max}
                        min={data.min}
                        max={limits.max}
                        step={1}
                        onChange={(d) => { if (data.max !== d.target.value) setData(old => ({ ...old, max: d.target.value })) }}
                        onBlur={() => {
                            let dmax = data.max;
                            if (dmax > limits.max) dmax = limits.max;
                            if (dmax < data.min) dmax = data.min;
                            setData(old => ({ ...old, min: dmax }));
                        }}
                    />
                    <span className="small text-secondary"><small>Maximum</small></span>
                </div>
            </div>
        </div>
    </div >
}