import React, { createContext, useEffect, useState } from "react";
import { useLocation } from 'react-router-dom';
import axios from "axios";

export const AuthContext = createContext();

export default function AuthProvider({ children }) {
    let refreshTimeout = null;
    let autoLogout = null;
    let logoutWatcher = null;
    let logoutPending = false;
    let exp = new Date();

    const location = useLocation();

    const [user, setUser] = useState(null);
    const [jwt, setJwt] = useState({ token: null, exp });
    const [refreshRequired, setRefreshRequired] = useState(false);

    async function logout() {
        if (jwt.token === null) return false;

        try {
            const response = await axios.get(process.env.API_ENDPOINT + '/logout', {
                headers: { Authorization: `Bearer ${jwt.token}` }
            });
        } catch (ex) {
            const message = ex.response?.data?.message || ex.message;
        } finally {
            setRefreshRequired(false);

            if (autoLogout !== null) {
                clearTimeout(autoLogout);
                autoLogout = null;
            }
            if (refreshTimeout !== null) {
                clearTimeout(refreshTimeout);
                refreshTimeout = null;
            }
            if (logoutWatcher !== null) {
                clearInterval(logoutWatcher);
                logoutWatcher = null;
            }

            exp = new Date();
            setJwt({ token: null, exp });
            setUser(null);

            logoutPending = false;

            return true;
        }
    }

    function expireAction() {
        if (!logoutPending) {
            const now = Date.now();
            if (exp.getTime() > 0 && exp.getTime() < now) {
                logoutPending = true;
                logout();
            }
        }
    }

    async function login(email, password) {
        try {
            const data = new FormData();
            data.set('email', email);
            data.set('password', password);

            const response = await axios.post(process.env.API_ENDPOINT + '/login', data);

            if (response.status !== 200) throw new Error(response.statusText);

            const ret = response.data;
            const now = Date.now();
            exp = new Date(ret.exp * 1000);
            const token = ret.jwt;
            const logoutDelay = (Math.abs(exp.getTime() - now) / 1000) - 120;

            setJwt(old => {
                return {
                    ...old,
                    token,
                    exp
                };
            });
            setUser(ret.user);

            setRefreshRequired(false);

            if (refreshTimeout === null) refreshTimeout = setTimeout(askForRefresh, logoutDelay * 1000)
            if (logoutWatcher === null) logoutWatcher = setInterval(expireAction, 30000);

            if (window && location.pathname !== '/') window.scrollTo(0, 0);

            return { error: null };
        } catch (ex) {
            const message = ex.response?.data?.message || ex.message;

            exp = new Date();
            setJwt({ token: null, exp });
            setUser(null);

            return { error: message };
        };
    }

    async function refresh() {
        if (jwt.token === null) return;

        try {
            const response = await axios.get(process.env.API_ENDPOINT + '/refresh', {
                headers: { Authorization: `Bearer ${jwt.token}` }
            });

            if (response.status !== 200) throw new Error(response.statusText);

            const ret = response.data;
            const now = Date.now();
            exp = new Date(ret.exp * 1000);
            const token = ret.jwt;
            const logoutDelay = (Math.abs(exp.getTime() - now) / 1000) - 120;

            setJwt(old => {
                return {
                    ...old,
                    token,
                    exp
                };
            });

            setRefreshRequired(false);

            if (refreshTimeout === null) refreshTimeout = setTimeout(askForRefresh, logoutDelay * 1000)
            if (logoutWatcher === null) logoutWatcher = setInterval(expireAction, 30000);
        } catch (ex) {
            const message = ex.response?.data?.message || ex.message;
            console.error(message);

            logout();
        };
    }

    function askForRefresh() {
        if (refreshTimeout !== null) {
            clearTimeout(refreshTimeout);
            refreshTimeout = null;
        }
        if (autoLogout !== null) {
            clearTimeout(autoLogout);
            autoLogout = null;
        }
        autoLogout = setTimeout(logout(), 120 * 1000);

        logoutPending = true;
        setRefreshRequired(true);
    }

    useEffect(() => {
        return (() => {
            if (autoLogout !== null) {
                clearTimeout(autoLogout);
                autoLogout = null;
            }
            if (refreshTimeout !== null) {
                clearTimeout(refreshTimeout);
                refreshTimeout = null;
            }
            if (logoutWatcher !== null) {
                clearInterval(logoutWatcher);
                logoutWatcher = null;
            }
        })
    }, []);

    return <AuthContext.Provider value={{ jwt, user, refreshRequired, logout, login, refresh }}>
        {children}
    </AuthContext.Provider>

}