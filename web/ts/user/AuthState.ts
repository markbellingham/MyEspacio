export default class AuthState
{
    private loggedIn = false;
    private username: string | null = null;

    constructor(loginButton: HTMLElement)
    {
        this.initFromDom(loginButton);
    }

    private initFromDom(loginButton: HTMLElement): void
    {
        const task = loginButton.dataset.task;
        this.loggedIn = task === "logout";
    }

    isLoggedIn(): boolean
    {
        return this.loggedIn;
    }

    getUsername(): string | null
    {
        return this.username;
    }

    setLoggedIn(username:string): void
    {
        this.loggedIn = true;
        this.username = username;
    }

    setLoggedOut(): void
    {
        this.loggedIn = false;
        this.username = null;
    }
}
