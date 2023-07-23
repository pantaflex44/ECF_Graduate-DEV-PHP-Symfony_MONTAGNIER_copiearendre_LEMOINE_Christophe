import React from "react";
import { Link } from "react-router-dom";

import ScrollToTop from "../components/ScrollToTop";
import Offers from "../components/Offers";


export default function AllOffers() {
    return <>
        <ScrollToTop />

        <nav aria-label="breadcrumb">
            <ol className="breadcrumb small mt-2">
                <li className="breadcrumb-item small"><small><Link to="/" className="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Accueil</Link></small></li>
                <li className="breadcrumb-item small active" aria-current="page"><small>Les occasions</small></li>
            </ol>
        </nav>
        <div className="row mt-5">
            <div className="col-12 mt-2 border-bottom border-light-subtle">
                <p className="h3">Trouvez la voiture d’occasion de vos rêves</p>
                <p className="small text-danger">
                    <small>A la recherche de votre nouvelle auto ? Notre garage vous propose des voitures d’occasion toutes marques. Tous les véhicules sont vérifiés, nettoyés et soumis à 143 points de contrôles. Roulez en toute sécurité.</small>
                </p>
            </div>
            <div className="col-12">
                <Offers preview={false} />
            </div>
        </div>
    </>
}