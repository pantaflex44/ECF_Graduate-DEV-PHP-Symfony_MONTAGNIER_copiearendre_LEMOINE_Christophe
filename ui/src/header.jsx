import React from "react";
import { Link } from "react-router-dom";

import LogoFull from "./assets/logo_full.png";

export default function Header() {
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
                            <li className="nav-item">
                                <Link className="nav-link active" aria-current="page" to={"/"}>Accueil</Link>
                            </li>
                            <li className="nav-item">
                                <a className="nav-link" href="#">Link</a>
                            </li>
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