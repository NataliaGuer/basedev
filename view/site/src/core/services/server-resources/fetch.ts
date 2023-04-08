import { FetchParameters, Address } from 'entities'

export class Fetch {

    protected parameters: FetchParameters;

    constructor(parameters: FetchParameters) {
        this.parameters = parameters;
    }

    protected async _fetchData(): Promise<any> {
        const { url, options } = this.parameters;
        fetch(url, options).then(
            res => {
                let data: Promise<any> | string = '';
                if (res.ok) {
                    try {
                        data = res.json();
                    } catch (error) {
                        console.log(error);
                    }
                } else {
                    data = 'fail';
                }
                return data;
            }
        ).then(
            (data: Promise<any> | string) => {
                if (data === 'fail') {
                    console.log('errore nella chiamata');
                } else {
                    return data;
                }
            }
        )
    }

    public async getData(): Promise<any> {
        return await this._fetchData()
    }

    public setParameters(parameters: FetchParameters) {
        this.parameters = parameters;
    }
}