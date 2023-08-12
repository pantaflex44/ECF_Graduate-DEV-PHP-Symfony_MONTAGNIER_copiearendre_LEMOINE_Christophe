import React, { useEffect, useState } from "react";

export default function StarRating({ initialValue = 2.5, starsCount = 5, onChange = null, disabled = false, id = null, size = 24, defaultCssColor = '#e9ecef', defaultDarkCssColor = '#adb5bd', cssColor = '#ffc107', ...props }) {
    const [selectedValue, setSelectedValue] = useState(0);
    const [value, setValue] = useState(0);
    const [hover, setHover] = useState(0);
    
    useEffect(() => {
        let iv = Number(initialValue);
        iv = Math.round(iv / 0.5) * 0.5;
        if (iv < 0) iv = 0;
        if (iv > starsCount) iv = starsCount;
        setSelectedValue(iv);
        setValue(iv);
    }, [initialValue]);

    return <div
        id={id}
        {...props}
        style={{
            display: 'flex',
            flexWrap: 'nowrap',
            padding: '2px'
        }}
        onMouseLeave={(e) => {
            setHover(0);
            setValue(selectedValue);
        }}
    >
        {
            Array.from(Array(starsCount * 2)).map((_, i) => {
                return <svg
                    key={`rating-halfstar-${i}`}
                    style={{
                        margin: 0,
                        padding: 0,
                        transform: i % 2 === 1 ? `scale(-1, 1) translateX(${size + ((i - 1) * size / 2.5)}px)` : `translateX(-${i * size / 2.5}px)`,
                        zIndex: `900${i % 2 === 0 ? 1 : 0}`,
                        cursor: disabled ? 'default' : 'pointer'
                    }}
                    width={`${size}px`}
                    height={`${size}px`}
                    viewBox="0 0 21 21"
                    version="1.1"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlnsXlink="http://www.w3.org/1999/xlink"
                    onMouseMove={(e) => {
                        if (!disabled) {
                            const bounds = e.target.getBoundingClientRect();
                            const x = e.clientX - bounds.left;
                            const y = e.clientY - bounds.top;
                            const v = ((i + 1) / 2) + (x > (size / 2) ? 0.5 : 0);
                            if (v <= value) {
                                setValue(v);
                                setHover(0);
                            } else {
                                setHover(v);
                                setValue(selectedValue);
                            }
                        }
                    }}
                    onClick={() => {
                        if (!disabled) {
                            const sv = hover > value ? hover : value;
                            setSelectedValue(sv);
                            setValue(sv);
                            if (onChange) onChange(sv);
                        }
                    }}

                >
                    <g id="halfstar" stroke="none" strokeWidth="1" fill="none" fillRule="evenodd">
                        <g transform="translate(-419.000000, -280.000000)" fill="#000000">
                            <g transform="translate(56.000000, 160.000000)">
                                <path
                                    style={{ fill: (i / 2) < value ? cssColor : ((i / 2) < hover ? defaultDarkCssColor : defaultCssColor), transition: 'fill 100ms' }}
                                    d="M374,120 L374,137.714 C374,137.714 373.571353,137.786 373.269101,137.93 L369.638788,139.779 C369.321149,139.931 368.99252,140 368.673782,140 C367.465876,140 366.399753,139.01 366.629464,137.791 L367.365858,133.876 C367.481263,133.264 367.254849,132.64 366.766851,132.206 L363.63223,129.433 C362.402341,128.342 363.066195,126.441 364.766496,126.217 L369.058465,125.645 C369.73331,125.556 370.258678,125.17 370.55983,124.614 L372.375536,121.051 C372.755824,120.35 372.900904,120 374,120"
                                ></path>
                            </g>
                        </g>
                    </g>
                </svg>
            })
        }
    </div>;
}