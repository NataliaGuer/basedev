import { RoutingElement } from "entities";
import { useMemo } from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";

import Homepage from "../../../features/homepage/homepage.component";
import PageNotFound from "../../../features/page-not-found/page-not-found.component";

const RoutesManager = () => {
    const routingElements: RoutingElement[] = useMemo(
        () => {
            return [
                {
                    route: '*',
                    component: PageNotFound
                },
                {
                    route: '/',
                    component: Homepage
                },
                {
                    route: '/ciao',
                    component: Homepage
                }
            ]
        }, []
    )
    return (
        <BrowserRouter>
            <Routes>
                {
                    routingElements.map(
                        element => <Route path={element.route} Component={element.component}></Route>
                    )
                }
            </Routes>
        </BrowserRouter>
    )
}

export default RoutesManager;