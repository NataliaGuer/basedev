import { RoutingElement } from "entities";
import { useMemo } from "react";
import { Route } from "react-router-dom";
import Homepage from "features/homepage/homepage.component";

const RoutesManager = () => {
    const routingElements: RoutingElement[] = useMemo(
        () => {
            return [
                {
                    route: '*',
                    component: Homepage
                },
                {
                    route: 'ciao',
                    component: Homepage
                }
            ]
        }, []
    )
    return (
        <>
            {routingElements.map(
                    element => <Route path={element.route} Component={element.component}></Route> 
                )
            }
        </>
    )
}

export default RoutesManager;