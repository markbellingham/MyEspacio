class Tooltips
{
    show()
    {
        [...document.querySelectorAll('[data-bs-toggle="tooltip"]')]
            .forEach(el => {
                console.log(el);
                return new bootstrap.Tooltip(el);
            });
    }
}

const tooltips = new Tooltips();
export default tooltips;