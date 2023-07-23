import React from "react";
import { Link } from "react-router-dom";

import ScrollToTop from "../components/ScrollToTop";
import Services from "../components/Services";


export default function AllServices() {
    return <>
        <ScrollToTop />
        
        <nav aria-label="breadcrumb">
            <ol className="breadcrumb small mt-2">
                <li className="breadcrumb-item small"><small><Link to="/" className="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Accueil</Link></small></li>
                <li className="breadcrumb-item small active" aria-current="page"><small>Toutes nos prestations</small></li>
            </ol>
        </nav>
        <div className="row mt-5">
            <div className="col-12 mt-2 border-bottom border-light-subtle">
                <p className="h3">Toutes nos prestations</p>
                <p className="small text-danger">
                    <small>Obtenez une prestation de qualité effectuée à l’aide de l’expertise de nos mécaniciens. {process.env.SITENAME} assure la mise en place d'un rendez-vous « sur-mesure » en fonction de vos besoins. Demandez dès maintenant votre devis et obtenez un RDV dans les plus brefs délais pour la réparation de votre voiture.</small>
                </p>
            </div>
            <div className="col-12">
                <Services preview={false} />
            </div>
        </div>
    </>
}