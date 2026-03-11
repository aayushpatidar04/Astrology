<script setup></script>

<template>
    <section class="relative py-12 px-6 md:px-12 flex flex-col md:flex-row items-center bg-[#fdf6f0] mt-2">
        <!-- Left Content -->
        <div class="md:w-1/2 space-y-6 text-center md:text-left">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800">
                What's Your Sign?
            </h2>

            <p class="text-lg text-gray-600">
                Read Your Daily Horoscope Today
            </p>

            <p class="text-gray-500">
                Discover the secrets written in the stars and unlock guidance for love, career, and personal growth.
                Every zodiac sign carries unique energy — let today’s horoscope inspire your decisions and bring clarity
                to your journey.
            </p>

            <p class="text-gray-500">
                Whether you’re seeking motivation, reassurance, or simply curious about what the universe has planned,
                our astrologers craft daily insights tailored to your sign. Explore cosmic wisdom, align with your
                destiny,
                and embrace the opportunities waiting for you.
            </p>

            <p class="text-gray-500">
                Astrology isn’t just prediction — it’s a mirror to your inner self. Step into a world of celestial
                guidance,
                where ancient knowledge meets modern life, and let your horoscope be the spark that lights your path
                today.
            </p>

            <!-- Buttons -->
            <div class="flex justify-center md:justify-start space-x-4">
                <button class="px-5 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                    READ MORE
                </button>
                <button class="px-5 py-2 border border-orange-500 text-orange-500 rounded hover:bg-orange-100">
                    GET STARTED
                </button>
            </div>
        </div>

        <!-- Right Zodiac Wheel -->
        <div class="md:w-1/2 mt-8 md:mt-0 flex justify-center relative">
            <img src="/images/zodiac-wheel.png" alt="Zodiac Wheel" class="w-100 animate-spin-slow" />
        </div>
    </section>
</template>

<style>
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}
</style>