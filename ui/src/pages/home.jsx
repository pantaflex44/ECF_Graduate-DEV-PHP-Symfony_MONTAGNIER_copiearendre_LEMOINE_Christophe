import React from "react";

import OpeningHours from "../components/OpeningHours";
import PhoneButton from "../components/PhoneButton";

import Gvp1 from "../assets/gvp1.jpg";
import Map1 from "../assets/map1.png";
import MessageButton from "../components/MessageButton";
import Services from "../components/Services";


export default function Home() {
    return <>
        <div className="row align-items-stretch g-0 mt-3 justify-content-center h-100">
            <div className="col-12 col-sm-12 col-lg-2">
                <img className="object-fit-cover" src={Gvp1} style={{ width: "100%", minHeight: "100%", maxHeight: "216px" }} />
            </div>
            <div className="col-12 col-sm-6 col-lg-4 bg-body-tertiary p-4">
                <p className="fs-3 text-uppercase fw-bolder">{process.env.SITENAME}</p>
                <p className="fs-6 text-uppercase">{process.env.POSTAL_ADDRESS}</p>
                <div className="py-2"><PhoneButton phoneNumber={process.env.PHONE} /></div>
                <div className="pt-2"><MessageButton text="Nous contacter" /></div>
            </div>
            <div className="col-12 col-sm-6 col-lg-4 bg-body-tertiary p-4">
                <p className="fs-6 text-uppercase fw-bolder">Horaires d'ouverture</p>
                <OpeningHours />
            </div>
            <div className="col-12 col-sm-6 col-lg-2 d-none d-lg-block">
                <img className="object-fit-cover" src={Map1} style={{ width: "100%", minHeight: "100%", maxHeight: "216px" }} />
            </div>
        </div>

        <div className="row mt-5">
            <div className="col-12 mt-2 border-bottom border-light-subtle">
                <p className="h3">Nos prestations</p>
                <p className="small text-danger">
                    <small>Nos mécaniciens s’occupent de la réparation et l’entretien de votre voiture, peu importe la marque ou le modèle de celle-ci. Profitez d’une prestation de qualité effectuée par des véritables experts auto. Demandez nous dès maintenant un devis pour la réparation de votre voiture et obtenez un RDV dans les plus brefs délais!</small>
                </p>
            </div>
            <div className="col-12">
                <Services preview={true} />
            </div>
        </div>

    </>;
}