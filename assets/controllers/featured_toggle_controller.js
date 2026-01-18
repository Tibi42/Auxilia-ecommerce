import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['star', 'form'];

    async toggle(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const url = form.action;
        const star = form.querySelector('i');

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.isFeatured) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                    star.style.color = '#fbc02d';
                    star.title = 'Retirer des vedettes';
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                    star.style.color = '#ccc';
                    star.title = 'Mettre en vedette';
                }
            }
        } catch (error) {
            console.error('Error toggling featured status:', error);
        }
    }
}
