import React, { useEffect, useState } from "react";

export default function FilterTextWithAutocomplete({ id, label, value = '', list = [], onChange = null, ...props }) {
    const [data, setData] = useState(value);

    useEffect(() => {
        if (data !== value) setData(value);
    }, []);

    useEffect(() => { if (onChange) onChange({ [id]: data.trim().split(',').map(o => o.trim()).filter(o => o !== '').join(', ').trim() }) }, [data]);

    function add(option) {
        const input = document.getElementById(`filter-${id}`);
        input.focus();

        if (!data.toLowerCase().split(',').map(o => o.trim()).includes(option.toLowerCase().trim())) {
            setData(old => {
                let txt = (old + `, ${option}`).trim();
                if (txt.startsWith(',')) txt = txt.slice(1).trim();
                return txt;
            });
        }
    }

    return <div className="mb-4" {...props}>
        <label htmlFor={`filter-${id}`} className="form-label small text-truncate w-100 mb-0">{label}</label>
        <div className="w-auto" style={{ position: "relative", boxSizing: "border-box" }}>
            <div style={{ alignItems: "center", cursor: "default", display: "flex", flexWrap: "wrap", justifyContent: "space-between", minHeight: "38px", outline: "0 !important", position: "relative", transition: "all 100ms", borderColor: "var(--bs-border-color)", borderRadius: "4px", borderStyle: "solid", borderWidth: "1px", boxShadow: "0", boxSizing: "border-box" }} className="bg-body text-body input-box filter-text-with-autocomplete-container">
                <div style={{ alignItems: "center", display: "grid", flex: "1", flexWrap: "wrap", position: "relative", overflow: "hidden", padding: "2px 8px", boxSizing: "border-box" }}>
                    <div style={{ gridArea: "1/1/2/3", color: "hsl(0, 0%, 50%)", marginLeft: "2px", marginRight: "2px", boxSizing: "border-box" }}></div>
                    <div style={{ display: "inline-grid", gridArea: "1/1/2/3", gridTemplateColumns: "0 100%", margin: "2px", paddingBottom: "2px", paddingTop: "2px", color: "hsl(0, 0%, 20%)", boxSizing: "border-box" }}>
                        <input
                            autoCapitalize="none" autoComplete="off" autoCorrect="off" spellCheck="false" role="combobox"
                            type="text"
                            name={id}
                            id={`filter-${id}`}
                            list={`filter-${id}-list`}
                            placeholder="[ , ] pour séparer manuellement les termes recherchés"
                            className={`filter-text-with-autocomplete-${id}`}
                            value={data}
                            onChange={(d) => { if (data !== d.target.value) setData(d.target.value) }}
                            style={{ color: "inherit", background: "0px center", opacity: "1", width: "100%", gridArea: "1/2/auto/auto", font: "inherit", minWidth: "2px", border: "0px", margin: "0px", outline: "0px", padding: "0px" }}
                        />
                    </div>
                </div>
                <div className="input-arrow-container" style={{ alignItems: "center", alignSelf: "stretch", display: "flex", flexShrink: "0", boxSizing: "border-box" }} data-bs-toggle="dropdown" aria-expanded="false">
                    <span style={{ alignSelf: "stretch", width: "1px", backgroundColor: "hsl(0, 0%, 80%)", marginBottom: "8px", marginTop: "8px", boxSizing: "border-box" }}></span>
                    <div style={{ display: "flex", transition: "color 150ms", color: "hsl(0, 0%, 80%)", padding: "8px", boxSizing: "border-box" }} className="input-arrow" aria-hidden="true">
                        <svg height="20" width="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false" style={{ display: "inline-block", fill: "currentcolor", stroke: "currentcolor", lineHeight: "1", strokeWidth: "0" }}>
                            <path d="M4.516 7.548c0.436-0.446 1.043-0.481 1.576 0l3.908 3.747 3.908-3.747c0.533-0.481 1.141-0.446 1.574 0 0.436 0.445 0.408 1.197 0 1.615-0.406 0.418-4.695 4.502-4.695 4.502-0.217 0.223-0.502 0.335-0.787 0.335s-0.57-0.112-0.789-0.335c0 0-4.287-4.084-4.695-4.502s-0.436-1.17 0-1.615z"></path>
                        </svg>
                    </div>
                </div>

                <ul className="dropdown-menu" id={`filter-${id}-list`} style={{ cursor: "default" }}>
                    <li><h6 className="dropdown-header text-wrap">Utilisez la virgule [ , ] pour séparer manuellement les termes recherchés</h6></li>
                    <li><hr className="dropdown-divider" /></li>
                    {list.map(l => <li key={l} className="dropdown-item small text-wrap py-2" onClick={() => add(l)}>{l}</li>)}
                </ul>
            </div>
        </div>
    </div>;
}