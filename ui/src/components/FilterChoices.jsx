import React, { createRef, useContext, useEffect, useRef, useState } from "react";
import { AuthContext } from "../providers/AuthProvider";
import Select from "react-select";

export default function FilterChoices({ id, label, values_descriptor = [], value = '', options = [], onChange = null, ...props }) {
    const auth = useContext(AuthContext);

    const [selector, setSelector] = useState([]);
    const [data, setData] = useState([]);

    const dataToChoice = (d) => d ? d.map(dd => dd.value).join(',') : '';

    useEffect(() => {
        const lbl = value.split(',').map(dv => (
            { label: values_descriptor[dv.trim()] ?? dv.trim(), value: dv.trim() }
        ));
        const toSet = value ? lbl : [];
        if (JSON.stringify(data) !== JSON.stringify(toSet)) setData(toSet);

        let d = [];
        if ((id === 'active' && (auth?.user?.role === "admin" || auth?.user?.role === "worker")) || id !== 'active') {
            d = options.map(option => {
                return { value: option, label: values_descriptor[option] ?? option };
            });
        }
        if (JSON.stringify(selector) !== JSON.stringify(d)) setSelector(d);
    }, []);

    useEffect(() => { if (onChange) onChange({ [id]: data.length > 0 ? dataToChoice(data) : null }) }, [data]);

    return <>
        <div className="mb-4" {...props}>
            <label htmlFor={`filter-${id}`} className="form-label small text-truncate w-100 mb-0">{label}</label>
            <Select
                options={selector}
                name={id}
                id={`filter-${id}`}
                value={data}
                onChange={(d) => {
                    let newData = d;
                    if (newData.length === 0) {
                        newData = value ? { label: values_descriptor[value] ?? value, value: value } : [];
                    }
                    if (JSON.stringify(data) !== JSON.stringify(newData)) setData(newData);
                }}
                isSearchable={true}
                isMulti={true}
                placeholder={"Vos choix"}
                noOptionsMessage={() => "aucun autre choix disponible"}
                styles={{
                    option: (baseStyles, state) => ({
                        ...baseStyles,
                        backgroundColor: state.isFocused ? 'rgba(var(--bs-tertiary-bg-rgb), 1) !important' : 'rgba(var(--bs-body-bg-rgb), 1)',
                    }),
                    control: (baseStyles, state) => ({
                        ...baseStyles,
                        borderColor: state.isFocused ? 'rgba(var(--bs-danger-rgb), 0.6) !important' : 'rgba(var(--bs-dark-rgb), 0.15)',
                        boxShadow: state.isFocused ? '0 0 0 0.1rem rgba(var(--bs-danger-rgb), var(--bs-focus-ring-opacity)) !important' : 'inherit',
                        "&:hover": {
                            borderColor: 'rgba(var(--bs-dark-rgb), 0.15)',
                            boxShadow: '0 !important'
                        }
                    })
                }}
                className="small w-auto"
            />
        </div>

    </>;
}