import React, { useState } from "react";

export default function PhoneButton({ phoneNumber }) {
    const [phone, setPhone] = useState({ state: 0, text: "Montrer le numéro de téléphone" });

    function togglePhone() {
        if (phone.state === 0) setPhone((phone) => { return { ...phone, state: 1, text: `✆ ${phoneNumber}` } })
        if (phone.state === 1) window.open(`tel:${phone.text}`);
    }

    return (
        <button type="button" className="btn btn-dark btn-sm w-100" onClick={() => togglePhone()}>{phone.text}</button>
    );
}