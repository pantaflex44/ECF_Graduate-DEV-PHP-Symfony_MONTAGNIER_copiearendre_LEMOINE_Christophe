import React, { useContext } from "react";
import { NavLink } from "react-router-dom";

import LogoFull from "./assets/logo_full.png";
import { AuthContext } from "./providers/AuthProvider";

export default function Header({ ...props }) {
    const routes = [
        { text: "Accueil", to: "/" },
        { text: "Nos prestations", to: "/prestations" },
        { text: "Les occasions", to: "/occasions" },
    ];

    const auth = useContext(AuthContext);

    return (
        <div className="sticky-top bg-body pb-2 px-3 border-bottom border-light-subtle" {...props}>
            <nav className="navbar navbar-expand-md bg-body mb-0 pb-0" style={{ maxWidth: "1320px", marginInline: "auto" }}>
                <div className="container-fluid mb-0">
                    <a className="navbar-brand" href="/">
                        <img src={LogoFull} alt="Garage Vincent Parrot" width={200} className="img-fluid" />
                    </a>

                    <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Ouvrir / Fermer le menu">
                        <span className="navbar-toggler-icon"></span>
                    </button>

                    <div className="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0 text-uppercase small align-items-center">
                            <li className="navbar-text ms-3 small text-body-emphasis text-capitalize">{process.env.SLOGAN}</li>

                            {routes.map(r =>
                                <li className="nav-item ms-3" key={r.to}>
                                    <NavLink className={({ isActive, isPending }) =>
                                        isPending ? "nav-link pending" : isActive ? "nav-link active fw-bolder bg-danger text-white rounded py-2 px-3" : "nav-link"
                                    } aria-current="page" to={r.to}>{r.text}</NavLink>
                                </li>
                            )}
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    );
}