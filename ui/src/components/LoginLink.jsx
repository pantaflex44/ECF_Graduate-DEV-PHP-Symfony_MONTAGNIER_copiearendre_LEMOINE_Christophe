import React, { useContext, useEffect, useState } from "react";
import { NavLink } from "react-router-dom";

import { AuthContext } from "../providers/AuthProvider";

import Alert from "./Alert";

export default function LoginLink() {
    const auth = useContext(AuthContext);

    const defaultForm = { email: '', password: '' };
    const [id, setId] = useState('loginModal');
    const [form, setForm] = useState({ ...defaultForm });
    const [sendError, setSendError] = useState('');
    const [logoutRequired, setLogoutRequired] = useState(false);

    function secondsToHumanReadable(seconds) {
        var levels = [
            [Math.floor(seconds / 31536000), 'années'],
            [Math.floor((seconds % 31536000) / 86400), 'jours'],
            [Math.floor(((seconds % 31536000) % 86400) / 3600), 'heures'],
            [Math.floor((((seconds % 31536000) % 86400) % 3600) / 60), 'minutes'],
            [(((seconds % 31536000) % 86400) % 3600) % 60, 'secondes'],
        ];
        var returntext = '';

        for (var i = 0, max = levels.length; i < max; i++) {
            if (levels[i][0] === 0) continue;
            returntext += ' ' + levels[i][0] + ' ' + (levels[i][0] === 1 ? levels[i][1].substr(0, levels[i][1].length - 1) : levels[i][1]);
        };
        return returntext.trim();
    }

    function handleChange(name, value) {
        setForm(old => {
            return { ...old, [name]: value };
        });
        setForm(old => {
            return {
                ...old,
                errors: null,
                submitting: false,
                submitted: false
            }
        });
    }

    function cancel() {
        setSendError('');
        setForm({ ...defaultForm });
    }

    function validateAndSubmit() {
        const emailIsValid = form.email.trim().match(
            /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        ) ? true : false;

        const isValid = (emailIsValid);

        const errors = isValid ? null : {
            'email': !emailIsValid
        };

        setForm(old => {
            return {
                ...old,
                errors,
                submitting: isValid,
                submitted: false
            }
        });
    }

    function handleSubmit(e) {
        e.preventDefault();
        setSendError('');
        validateAndSubmit();
    }

    async function finishSubmit() {
        const ret = await auth.login(form.email.trim(), form.password.trim());

        if (ret.error) {
            setSendError(ret.error);

            setForm(old => {
                return {
                    ...old,
                    errors: null,
                    submitting: false,
                    submitted: false
                }
            });
        } else {
            setForm(old => {
                return {
                    ...old,
                    errors: null,
                    submitting: false,
                    submitted: true
                }
            });
        }
    }

    function logout() {
        if (auth) {
            setLogoutRequired(!auth.logout());
            if (window) window.scrollTo(0, 0);
        }
    }

    function cancelLogout() {
        setLogoutRequired(false);
    }

    function requireLogout() {
        setLogoutRequired(true);
    }

    function refresh() {
        if (auth) auth.refresh();
    }

    useEffect(() => {
        if (form.submitting && form.errors === null) {
            finishSubmit();
        }
    }, [form.submitting]);

    useEffect(() => setId(`loginModal_${(Math.random() + 1).toString(36).substring(7)}`), []);

    return <>
        {!auth?.user
            ? <button type="button" className="btn btn-link link-light link-offset-2 link-underline-opacity-0 d-flex align-items-center" data-bs-toggle="modal" data-bs-target={`#${id}`}><span className="fw-bold text-decoration-none me-2">⋇</span><span className="small">Staf or not Staf?</span></button>
            : <>
                <div>
                    <span className="fw-bold small">Bonjour {auth.user.display_name}!</span> <button className="btn btn-link btn-sm text-danger" onClick={() => requireLogout()}><small>Me déconnecter</small></button>
                </div>
            </>
        }

        <div className="modal fade" id={id} tabIndex="-1" aria-labelledby="loginModalLabel" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered modal-sm text-body fg-body">
                <div className="modal-content">
                    <form onSubmit={handleSubmit}>
                        <div className="modal-header">
                            <h1 className="modal-title fs-5" id={`loginModalLabel-${id}`}>Connexion</h1>
                            <button type="button" className="btn-close btn-sm small" data-bs-dismiss="modal" aria-label="Annuler" disabled={form.submitting} onClick={() => cancel()}></button>
                        </div>
                        <div className="modal-body row g-3 pb-4">
                            <div className="col-12">
                                <label htmlFor={`from-${id}`} className="form-label col-form-label-sm">Email</label>
                                <input type="email" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id={`email-${id}`} placeholder="adresse@email.com" value={form.email} onChange={(e) => handleChange('email', e.target.value)} />
                                {form.errors && form.errors.email &&
                                    <div id={`login-email-error-${id}`} className="form-text small text-danger">
                                        <small>Adresse email absente ou incorrecte!</small>
                                    </div>
                                }
                            </div>
                            <div className="col-12">
                                <label htmlFor={`password-${id}`} className="form-label col-form-label-sm">Mot de passe</label>
                                <input type="password" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id={`password-${id}`} placeholder=" " value={form.password} onChange={(e) => handleChange('password', e.target.value)} />
                            </div>
                            {sendError &&
                                <div className="col-12">
                                    <Alert title="Erreur de connexion" message={sendError} />
                                </div>
                            }
                            {form.submitted &&
                                <div className="col-12">
                                    <Alert title="Connexion réussie!" message={`Bienvenue ${auth.user.display_name}. Prudence, vous pouvez désormais modifier le contenu de cet espace web! Cette session devra être renouvelée dans ${secondsToHumanReadable(process.env.JWT_LIVE)}.`} type="alert-success" />
                                </div>
                            }
                        </div>
                        <div className="modal-footer">
                            {form.submitted
                                ? <button type="button" className="btn btn-dark btn-sm px-4" data-bs-dismiss="modal" onClick={() => cancel()}>Fermer</button>
                                : <>
                                    <button type="button" className="btn btn-outline-dark btn-sm px-4" data-bs-dismiss="modal" disabled={form.submitting || form.submitted} onClick={() => cancel()}>Annuler</button>
                                    <button type="submit" className="btn btn-dark btn-sm px-4" disabled={form.submitting || form.submitted}>
                                        {form.submitting && <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>}
                                        {form.submitting ? 'Connexion...' : 'Connecter'}
                                    </button>
                                </>
                            }
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div className={`modal fade ${logoutRequired ? 'show' : 'hide'}`} style={logoutRequired ? { display: 'block' } : null} id={"logout-modal"} tabIndex="-1" aria-labelledby="logoutModalLabel" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered modal-sm text-body fg-body">
                <div className="modal-content">
                    <div className="modal-header">
                        <h1 className="modal-title fs-5" id="logoutModalLabel">Déconnexion demandée.</h1>
                        <button type="button" className="btn-close" aria-label="Fermer" onClick={() => cancelLogout()}></button>
                    </div>
                    <div className="modal-body">
                        <p className="small">Êtes-vous certain de vouloir vous déconnecter?</p>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-outline-dark btn-sm px-4" onClick={() => cancelLogout()}>Annuler</button>
                        <button type="button" className="btn btn-danger btn-sm px-4" onClick={() => logout()}>Valider</button>
                    </div>
                </div>
            </div>
        </div>

        <div className={`modal fade ${auth?.refreshRequired === true ? 'show' : 'hide'}`} style={auth?.refreshRequired === true ? { display: 'block' } : null} id={"refresh-modal"} tabIndex="-1" aria-labelledby="refreshModalLabel" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered modal-sm text-body fg-body">
                <div className="modal-content">
                    <div className="modal-header">
                        <h1 className="modal-title fs-5" id="refreshModalLabel">Attention requise.</h1>
                        <button type="button" className="btn-close" aria-label="Fermer" onClick={() => logout()}></button>
                    </div>
                    <div className="modal-body">
                        <p className="small">Votre session se termine dans moins de 2 minutes. Désirez-vous la prolonger?</p>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-outline-dark btn-sm px-4" onClick={() => logout()}>Non</button>
                        <button type="button" className="btn btn-dark btn-sm px-4" onClick={() => refresh()}>Oui</button>
                    </div>
                </div>
            </div>
        </div>

        {(logoutRequired || auth?.refreshRequired === true) && <div className="modal-backdrop fade show"></div>}
    </>
}