import React from "react";
import { NavLink } from "react-router-dom";

import LogoFull from "./assets/logo_full.png";

export default function Header() {
    const routes = [
        { text: "Accueil", to: "/" },
        { text: "Prestations", to: "/prestations" },
    ];

    return (
        <>
            <nav className="navbar navbar-expand-md bg-body sticky-top border-bottom border-secondary-subtle">
                <div className="container-fluid">
                    <a className="navbar-brand" href="/">
                        <img src={LogoFull} alt="Garage Vincent Parrot" width={200} className="img-fluid" />
                    </a>
                    <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Ouvrir / Fermer le menu">
                        <span className="navbar-toggler-icon"></span>
                    </button>
                    <div className="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                            {routes.map(r =>
                                <li className="nav-item">
                                    <NavLink className={({ isActive, isPending }) =>
                                        isPending ? "nav-link pending" : isActive ? "nav-link active fw-bolder" : "nav-link"
                                    } aria-current="page" to={r.to}>{r.text}</NavLink>
                                </li>
                            )}
                        </ul>
                    </div>
                    <span className="navbar-text text-uppercase">
                        {process.env.SLOGAN}
                    </span>
                </div>
            </nav>
        </>
    );
}