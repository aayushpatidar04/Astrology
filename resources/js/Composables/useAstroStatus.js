import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

export function useAstroStatus() {
    const page = usePage();
    const localOnline = ref(!!page.props.auth?.user?.astrologer?.online);

    watch(
        () => page.props.auth?.user?.astrologer?.online,
        (val) => {
            localOnline.value = !!val;
        }
    );

    const online = computed({
        get: () => localOnline.value,
        set: (val) => {
            localOnline.value = !!val;
        }
    });

    const toggling = ref(false);

    const toggleStatus = async (alertRef = null) => {
        if (toggling.value) return;
        toggling.value = true;
        const nextState = !localOnline.value;
        localOnline.value = nextState;

        try {
            await axios.post("/astrologer/status", {
                online: nextState ? 1 : 0,
            });
            router.reload({ only: ['auth'] });
            if (nextState) {
                alertRef?.showAlert("You are now online and available for chats!", "success");
            } else {
                alertRef?.showAlert("You are now offline. Users cannot reach you.", "success");
            }
        } catch (error) {
            localOnline.value = !nextState;
            alertRef?.showAlert("Something went wrong, please try after some time!", "error");
        } finally {
            toggling.value = false;
        }
    };

    return {
        online,
        toggling,
        toggleStatus,
    };
}
