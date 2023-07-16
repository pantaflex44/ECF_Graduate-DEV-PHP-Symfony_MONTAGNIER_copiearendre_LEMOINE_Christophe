import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import axios from 'axios';
import useDeviceSize from '../hooks/useDeviceSize';

import Alert from "./Alert";
import MessageButton from "./MessageButton";

export default function Services({ preview = false }) {
    let mounted = false;
    let loading = false;

    const [allServices, setAllServices] = useState({ error: null, list: [] });
    const [services, setServices] = useState({ list: [], cardWidth: 0 })

    const deviceSize = useDeviceSize();
    let lastDeviceWidth = 0;

    async function getServices() {
        if (loading) return;
        loading = true;

        axios.get(process.env.API_ENDPOINT + '/services')
            .then(response => {
                if (mounted && response.status === 200) {
                    setAllServices(old => {
                        return {
                            ...old,
                            error: null,
                            list: response.data
                        }
                    });
                }
            })
            .catch(ex => {
                let message = ex.message;
                switch (ex.request.status) {
                    case 500:
                        message = "Une erreur interne s'est produite!";
                        break;
                }
                setAllServices(old => {
                    return {
                        ...old,
                        error: `(#${ex.request.status}) ${message}`,
                        list: []
                    }
                });
            })
            .finally(() => loading = false);
    }

    function arrangeServices() {
        let count = allServices.list.length;
        if (count === 0 || allServices.error !== null) {
            setServices(old => { return { ...old, list: [], cardWidth: 0 } });
            return;
        }

        let cardWidth = 300;
        let maxLayoutWidth = 1320;
        let deviceWidth = deviceSize.width;
        if (deviceWidth > maxLayoutWidth) deviceWidth = maxLayoutWidth;
        const perRow = Math.floor(deviceWidth / cardWidth);
        cardWidth = Math.floor(deviceWidth / perRow);
        if (preview) count = perRow;
        if (deviceWidth <= (cardWidth * 2) && count === 1) count += 1;

        let list = allServices.list.sort(() => { return .5 - Math.random() });
        list = list.slice(0, count);
        setServices(old => {
            return {
                ...old,
                list,
                cardWidth: `calc(calc(100% / ${perRow}px) - 1rem)`
            }
        });
    }

    useEffect(() => {
        mounted = true;
        getServices();
        return (() => { mounted = false; })
    }, []);

    useEffect(() => {
        if (deviceSize.width === lastDeviceWidth) return;
        lastDeviceWidth = deviceSize.width;

        arrangeServices();
    }, [allServices, deviceSize.width]);

    return (
        <>
            {allServices.error
                ? <Alert title="Impossible de récupérer la liste des prestations!" message={error} />
                : <div className="mb-5">
                    <div className="card-group border border-0 small">
                        {services.list.map((entry, i) => {
                            return <div key={`service_${i}`} className="card border border-0 mx-2 my-4" style={{ width: services.cardWidth, minWidth: services.cardWidth, maxWidth: services.cardWidth }}>
                                <img src={entry.image} className="card-img-top border object-fit-cover" alt={entry.name} style={{ height: "200px" }} />
                                <div className="card-body">
                                    <h6 className="card-title fw-bolder">{entry.name}</h6>
                                    <p className="card-text">{entry.description}</p>
                                </div>
                                <ul className="list-group list-group-flush">
                                    <li className="list-group-item text-danger fw-bolder">A partir de {entry.amount}€</li>
                                </ul>
                                <div className="card-footer py-3">
                                    <MessageButton text="Demander un devis" subject={`[${entry.name}] Demande de devis`} subjectReadonly={true} message={`Bonjour,\r\n\r\nJe souhaiterai un devis pour la prestation ${entry.name}.\r\n\r\nMarque du véhicule:\r\nModèle:\r\nKilométrage:\r\nAnnée de mise en circulation:\r\nPlaque d'immatriculation:\r\n\r\nCordialement.`} />
                                </div>
                            </div>;
                        })}
                    </div>
                    {(services.list.length < allServices.list.length) && <p className="text-end fw-bolder small px-3 mt-3">❯ <Link to="/prestations" className="link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Toutes nos prestations ({allServices.list.length})</Link></p>}
                </div>
            }
        </>
    );
}