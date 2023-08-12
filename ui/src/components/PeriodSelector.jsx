import React, { useContext, useEffect, useState } from "react";
import axios from 'axios';

import { AuthContext } from "../providers/AuthProvider";

import Alert from "./Alert";

export default function PeriodSelector({ id, dayOkWeek, open, close }) {
    const auth = useContext(AuthContext);

    const [period, setPeriod] = useState({
        open: open.replace('h', ':'),
        close: close.replace('h', ':')
    });

    function handleOpenChange(e) {
        setPeriod(old => ({ ...old, open: e.target.value }));
    }

    function handleCloseChange(e) {
        setPeriod(old => ({ ...old, close: e.target.value }));
    }

    return <form>
        <div className="hstack gap-2">
            <span className="small fw-light">de</span>
            <input type="time" id={`${id}-open`} name={`${id}-open`} value={period.open} onChange={handleOpenChange} />

            <span className="small fw-light ms-2">à</span>
            <input type="time" id={`${id}-close`} name={`${id}-close`} value={period.close} onChange={handleCloseChange} />
        </div>
    </form>;
}