import React, { useContext } from "react";
import { NavLink } from "react-router-dom";

import { AuthContext } from "./providers/AuthProvider";

import LoginLink from "./components/LoginLink";

export default function Footer({...props}) {
    const auth = useContext(AuthContext);

    return <footer className="bg-dark text-white m-0 p-5" {...props}>
        <div style={{ maxWidth: "1320px", marginInline: "auto" }}>
            <LoginLink />
        </div>
    </footer>
}