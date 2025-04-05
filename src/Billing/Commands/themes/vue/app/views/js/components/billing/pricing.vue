<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { cn } from '@/utils';

const props = usePage().props;
const { auth, billing } = props;
const billingInterval = ref(billing.periods[0]);
const tiers = billing.tiers;

// Group tiers by name and billing period
const groupedTiers = computed(() => {
    const result = {};

    if (!tiers || Object.keys(tiers).length === 0) return {};

    Object.values(tiers).forEach((tier) => {
        if (!result[tier.name]) {
            result[tier.name] = {
                name: tier.name,
                description: tier.description,
                features: tier.features,
                periods: {},
            };
        }

        result[tier.name].periods[tier.billingPeriod] = {
            price: tier.price,
            id: tier.id,
            link: tier.link,
            currency: tier.currency || 'usd',
            popular: tier.popular,
            trialDays: tier.trialDays
        };
    });

    return result;
});

// Convert to array and sort by price (lowest first)
const tiersArray = computed(() => {
    return Object.values(groupedTiers.value).sort((a, b) => {
        const aPrice = a.periods[billingInterval.value]?.price || 0;
        const bPrice = b.periods[billingInterval.value]?.price || 0;
        return aPrice - bPrice;
    });
});

// Helper functions
const getTierData = (tier) => {
    return tier.periods[billingInterval.value] || {};
};

const isHighlighted = (tier) => {
    return getTierData(tier).popular;
};

const formatCurrency = (price, currency) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <div class="bg-gradient-to-b from-gray-900 to-black py-20 rounded-lg">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl sm:text-center">
                <span
                    class="inline-flex items-center rounded-md bg-indigo-500/10 px-3 py-1 text-sm font-medium text-indigo-400 ring-1 ring-inset ring-indigo-500/20 mb-4">
                    Subscription Plans
                </span>
                <h2
                    class="text-3xl font-bold tracking-tight text-white sm:text-4xl bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">
                    Choose your plan
                </h2>
                <p class="mt-6 text-lg leading-8 text-gray-300">
                    Select the perfect plan for your needs with flexible billing options.
                </p>
            </div>

            <div class="mt-10 flex justify-center">
                <div
                    class="relative flex rounded-full bg-gray-800/40 p-1.5 backdrop-blur-sm shadow-lg border border-gray-700/50">
                    <button type="button"
                        :class="`${billingInterval === 'monthly' ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 shadow-md shadow-indigo-500/20' : 'hover:bg-gray-700/50 text-gray-300'} rounded-full py-2.5 px-8 text-sm font-medium text-white focus:outline-none transition-all duration-200 ease-in-out`"
                        @click="billingInterval = 'monthly'">
                        Monthly
                    </button>
                    <button type="button"
                        :class="`${billingInterval === 'yearly' ? 'bg-gradient-to-r from-indigo-600 to-indigo-500 shadow-md shadow-indigo-500/20' : 'hover:bg-gray-700/50 text-gray-300'} ml-1 rounded-full py-2.5 px-8 text-sm font-medium text-white focus:outline-none transition-all duration-200 ease-in-out`"
                        @click="billingInterval = 'yearly'">
                        Yearly
                        <!-- you can add other touches like this -->
                        <!-- <span class="ml-1.5 inline-flex items-center rounded-full bg-indigo-800 px-2 py-0.5 text-xs font-medium text-white">
              Save 20%
            </span> -->
                    </button>
                </div>
            </div>

            <!-- Pricing cards -->
            <div
                :class="`mx-auto mt-16 grid max-w-md grid-cols-1 gap-10 lg:max-w-7xl ${tiersArray.length === 2 ? 'lg:grid-cols-2' : 'lg:grid-cols-3'}`">
                <div class="h-full" v-for="(tier, index) in tiersArray" :key="tier.name">
                    <div :class="`relative group h-full ${isHighlighted(tier) ? 'z-10 scale-105' : ''}`">
                        <div
                            :class="`absolute -inset-px bg-gradient-to-r ${isHighlighted(tier) ? 'from-purple-600 to-indigo-600 opacity-60' : 'from-indigo-500 to-purple-600 opacity-0'} group-hover:opacity-${isHighlighted(tier) ? '90' : '80'} rounded-3xl blur-[6px] transition duration-300 group-hover:duration-200`">
                        </div>
                        <div
                            :class="`relative bg-gradient-to-b from-gray-800/80 to-gray-900/90 p-7 py-10 rounded-3xl border ${isHighlighted(tier) ? 'border-indigo-500/50' : 'border-gray-700/50'} shadow-xl backdrop-blur-sm h-full`">
                            <div v-if="isHighlighted(tier)" class="absolute -top-4 left-0 right-0 flex justify-center">
                                <span
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-1 text-xs font-semibold uppercase tracking-wider text-white rounded-full shadow-md shadow-indigo-500/20">
                                    Most Popular
                                </span>
                            </div>

                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-xl font-bold text-white">
                                        {{ tier.name }}
                                    </h2>
                                    <p class="text-sm text-indigo-200/70 mt-1">
                                        {{ tier.description }}
                                    </p>
                                </div>
                                <div
                                    :class="`h-10 w-10 rounded-full ${isHighlighted(tier) ? 'bg-indigo-500/20' : 'bg-indigo-500/10'} flex items-center justify-center`">
                                    <!-- you can add icons like this. We've set up some for popular tier names -->
                                    <svg v-if="tier.name === 'Pro' || tier.name === 'professional'"
                                        class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <svg v-else-if="tier.name === 'Starter' || tier.name === 'Basic'"
                                        class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-indigo-400" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                </div>
                            </div>


                            <div class="relative flex flex-col bg-black/30 p-6 rounded-2xl border shadow-inner"
                                :class="isHighlighted(tier) ? 'border-indigo-500/30' : 'border-gray-800/50'">
                                <div class=" flex justify-center items-center mb-6">
                                    <span class="text-5xl font-bold text-white">
                                        <!-- you can update the minimumFractionDigits to display in the format 10.00 -->
                                        {{ formatCurrency(getTierData(tier).price || 0, getTierData(tier).currency) }}
                                    </span>
                                    <span class="ml-2 text-sm text-indigo-200/70">
                                        <span class="font-medium text-indigo-300">
                                            per {{ billingInterval.replace('ly', '') }}
                                        </span>
                                        <br />
                                        <span>
                                            plus local taxes
                                        </span>
                                    </span>
                                </div>

                                <a :href="getTierData(tier).link || '#'" :class="[
                                    'group/button relative overflow-hidden rounded-lg block',
                                    isHighlighted(tier)
                                        ? 'bg-gradient-to-r from-indigo-500 to-purple-600'
                                        : 'bg-gradient-to-r from-indigo-500 to-indigo-600',
                                    'px-4 py-3 shadow-md transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/25 text-center',
                                    auth.user.subscription?.plan_id === getTierData(tier).id
                                        ? 'cursor-not-allowed'
                                        : 'cursor-pointer',
                                ]" @click="(e) => {
                                if (auth.user.subscription?.plan_id === getTierData(tier).id) {
                                    e.preventDefault();
                                }
                            }">
                                    <div
                                        class="relative z-10 flex items-center justify-center text-sm font-semibold text-white">
                                        <span v-if="auth.user.subscription?.plan_id === getTierData(tier).id"
                                            class="text-white">
                                            Current Plan
                                        </span>
                                        <template v-else>
                                            {{ auth.user.hasSubscription ? 'Switch to plan' : 'Subscribe now' }}
                                            <svg class="ml-2 h-4 w-4 transition-transform duration-300 group-hover/button:translate-x-1"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div
                                        :class="`absolute inset-0 z-0 ${isHighlighted(tier) ? 'bg-gradient-to-r from-purple-600 to-indigo-600' : 'bg-gradient-to-r from-indigo-600 to-indigo-500'} opacity-0 transition-opacity duration-300 group-hover/button:opacity-100`">
                                    </div>
                                </a>

                                <p class="mt-4 flex justify-center text-sm text-indigo-200/60 sm:space-x-2">
                                    <span>
                                        {{ getTierData(tier).trialDays && !auth.user.hasSubscription
                                            ? `${getTierData(tier).trialDays} days free trial`
                                            : 'Cancel anytime. No setup fees.' }}
                                    </span>
                                </p>
                            </div>

                            <h3 class="sr-only">
                                {{ tier.name }} plan features
                            </h3>

                            <ul class="mt-8 space-y-5 text-sm text-indigo-200/70">
                                <li class="flex gap-3" v-for="(feature, featureIndex) in tier.features"
                                    :key="featureIndex">
                                    <div
                                        :class="`flex-shrink-0 flex h-6 w-6 items-center justify-center rounded-full ${isHighlighted(tier) ? 'bg-indigo-500/20' : 'bg-indigo-500/10'} text-indigo-400`">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p>
                                        <strong class="font-semibold text-white">
                                            {{ feature.title ?? feature.description }}
                                        </strong>
                                        <span v-if="feature?.title && feature?.description"
                                            class="block text-indigo-200/60 mt-1">
                                            {{ feature.description }}
                                        </span>
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
