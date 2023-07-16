import React, { useEffect, useState } from "react";
import axios from 'axios';

import Alert from "./Alert";

export default function MessageButton({ text, subject = "", subjectReadonly = false, message = "", ...props }) {
    const [id, setId] = useState('messageModal');
    const defaultForm = { from: '', forname: '', lastname: '', phone: '', subject, message, submitting: false, submitted: false, errors: null };
    const [form, setForm] = useState({ ...defaultForm });
    const [sendError, setSendError] = useState('');

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
        const fromIsValid = form.from.trim().match(
            /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        ) ? true : false;
        const phoneIsValid = form.phone.trim().match(
            /^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/
        ) ? true : false;
        const subjectIsValid = form.subject.trim().length >= 2;
        const messageIsValid = form.message.trim().length >= 2;
        const lastnameIsValid = form.lastname.trim().length >= 2;
        const fornameIsValid = form.forname.trim().length >= 2;

        const isValid = (fromIsValid && subjectIsValid && messageIsValid && phoneIsValid && lastnameIsValid && fornameIsValid);

        const errors = isValid ? null : {
            'from': !fromIsValid,
            'subject': !subjectIsValid,
            'message': !messageIsValid,
            'phone': !phoneIsValid,
            'lastname': !lastnameIsValid,
            'forname': !fornameIsValid
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

    function finishSubmit() {
        const data = new FormData();
        data.set('email', form.from.trim());
        data.set('subject', form.subject.trim());
        data.set('message', form.message.trim());
        data.set('name', form.lastname.trim());
        data.set('forname', form.forname.trim());
        data.set('phone', form.phone.trim());

        axios.post(process.env.API_ENDPOINT + '/contact', data)
            .then((response) => {
                if (response.status !== 200) throw new Error(response.statusText);

                setForm(old => {
                    return {
                        ...old,
                        errors: null,
                        submitting: false,
                        submitted: true
                    }
                });
            })
            .catch((ex) => {
                const message = ex.response?.data?.message || ex.message;
                setSendError(message);

                setForm(old => {
                    return {
                        ...old,
                        errors: null,
                        submitting: false,
                        submitted: false
                    }
                });
            });
    }

    useEffect(() => {
        if (form.submitting && form.errors === null) {
            finishSubmit();
        }
    }, [form.submitting]);

    useEffect(() => setId(`messageModal_${(Math.random() + 1).toString(36).substring(7)}`), []);

    return (
        <>
            <button type="button" className="btn btn-outline-dark btn-sm w-100" data-bs-toggle="modal" data-bs-target={`#${id}`} {...props}>{text}</button>

            <div className="modal fade" id={id} tabIndex="-1" aria-labelledby="messageModalLabel" data-bs-backdrop="static" data-bs-keyboard="true" aria-hidden="true">
                <div className="modal-dialog modal-dialog-centered">
                    <div className="modal-content">
                        <form onSubmit={handleSubmit}>
                            <div className="modal-header">
                                <h1 className="modal-title fs-5" id="messageModalLabel">{text}</h1>
                                <button type="button" className="btn-close btn-sm small" data-bs-dismiss="modal" aria-label="Fermer" disabled={form.submitting} onClick={() => cancel()}></button>
                            </div>
                            <div className="modal-body row g-3">
                                <div className="col-12">
                                    <label htmlFor="from" className="form-label col-form-label-sm">De</label>
                                    <input type="email" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id="from" placeholder="adresse@email.com" value={form.from} onChange={(e) => handleChange('from', e.target.value)} />
                                    {form.errors && form.errors.from &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Adresse email absente ou incorrecte!</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-md-6">
                                    <label htmlFor="lastname" className="form-label col-form-label-sm">Nom de famille</label>
                                    <input type="text" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger text-uppercase" id="lastname" placeholder=" " value={form.lastname} onChange={(e) => handleChange('lastname', e.target.value)} />
                                    {form.errors && form.errors.lastname &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Nom de famille absent ou incorrect!</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-md-6">
                                    <label htmlFor="forname" className="form-label col-form-label-sm">Prénom</label>
                                    <input type="text" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger text-capitalize" id="forname" placeholder=" " value={form.forname} onChange={(e) => handleChange('forname', e.target.value)} />
                                    {form.errors && form.errors.forname &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Prénom absent ou incorrect!</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-12">
                                    <label htmlFor="phone" className="form-label col-form-label-sm">Numéro de téléphone</label>
                                    <input type="tel" disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id="phone" placeholder=" " value={form.phone} onChange={(e) => handleChange('phone', e.target.value)} />
                                    {form.errors && form.errors.phone &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Numéro de téléphone absent ou incorrect!</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-12">
                                    <label htmlFor="subject" className={`form-label col-form-label-sm ${subjectReadonly ? 'text-secondary' : ''}`.trim()}>Sujet</label>
                                    <input type="text" disabled={form.submitting || form.submitted} className={`${subjectReadonly ? 'form-control-plaintext' : 'form-control'} form-control-sm focus-ring-danger`.trim()} id="subject" placeholder=" " value={form.subject} onChange={(e) => handleChange('subject', e.target.value)} readOnly={subjectReadonly} />
                                    {form.errors && form.errors.subject &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Le sujet doit contenir au moins un mot...</small>
                                        </div>
                                    }
                                    {!subjectReadonly &&
                                        <div id="subject-message" className="form-text small">
                                            <small>Le sujet du message doit être le plus explicite possible pour que nous puissions vous répondre efficacement.</small>
                                        </div>
                                    }
                                </div>
                                <div className="col-12">
                                    <label htmlFor="message" className="form-label col-form-label-sm">Message</label>
                                    <textarea disabled={form.submitting || form.submitted} className="form-control form-control-sm focus-ring-danger" id="message" rows="7" placeholder=" " value={form.message} onChange={(e) => handleChange('message', e.target.value)} />
                                    {form.errors && form.errors.message &&
                                        <div id="subject-message" className="form-text small text-danger">
                                            <small>Le message doit contenir au moins un mot...</small>
                                        </div>
                                    }
                                </div>
                                {sendError &&
                                    <div className="col-12">
                                        <Alert title="Impossible d'envoyer le message" message={sendError} />
                                    </div>
                                }
                                {form.submitted &&
                                    <div className="col-12">
                                        <Alert title="Merci!" message="Votre message vient d'être posté. Nous vous répondrons dans les plus bref délais." type="alert-success" />
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
                                            {form.submitting ? 'Envoie...' : 'Envoyer'}
                                        </button>
                                    </>
                                }
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}