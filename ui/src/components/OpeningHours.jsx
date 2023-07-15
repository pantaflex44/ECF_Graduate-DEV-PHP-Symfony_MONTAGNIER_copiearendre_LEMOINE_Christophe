import React, { useEffect, useState } from "react";
import axios from 'axios';

import Alert from "./Alert";

export default function OpeningHours() {
    let mounted = false;
    const [hours, setHours] = useState({ error: null, list: {} })

    async function getOpeningHours() {
        axios.get(process.env.API_ENDPOINT + '/openings')
            .then(response => {
                if (mounted && response.status === 200) {
                    let days = [];
                    response.data.forEach(day => {
                        days[day.dayOfWeek] = day.hours.map(hs => {
                            return { 'open': hs.open, 'close': hs.close };
                        });
                    });
                    setHours(old => {
                        return {
                            ...old,
                            error: null,
                            list: {
                                lun: days[1],
                                mar: days[2],
                                mer: days[3],
                                jeu: days[4],
                                ven: days[5],
                                sam: days[6],
                                dim: days[0],
                            }
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
                setHours(old => { return { ...old, error: `(#${ex.request.status}) ${message}`, list: {} } });
            })
    }

    useEffect(() => {
        mounted = true;
        getOpeningHours();
        return (() => { mounted = false; })
    }, []);

    return (
        <>
            {hours.error
                ? <Alert title="Impossible de récupérer les horaires d'ouverture!" message={error} />
                : Object.entries(hours.list).map((entry, i) => {
                    const [key, value] = entry;
                    const isDay = ((new Date()).getDay() - 1 === i ? 'text-danger' : '').trim();
                    const row = (content) => <div key={`${key}`} className={`row small text-uppercase ${isDay}`}>{content}</div>
                    const dn = <div key={`${key}_details`} className="col fw-bolder">{key.toUpperCase()}.</div>;
                    if (value.length === 0) return row(<>{dn}<div key={`${key}_closed`} className="col text-capitalize">Fermé</div></>);
                    else {
                        o = value.map((v, i) => {
                            return <div key={`${key}_${i}`} className="col text-lowercase">{`${v.open} - ${v.close}`}</div>
                        });
                        return row(<>{dn}{o}</>);
                    }
                })
            }
        </>
    );
}