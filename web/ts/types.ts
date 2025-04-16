export interface HttpRequestInterface
{
    query(url: string, options: RequestInit): Promise<unknown>;
}

export interface RequestHeadersInterface
{
    html(): Headers;
    json(): Headers;
    jsonWithToken(): Headers;
}
