import { useState, useMemo } from "react";
import { Link, usePage } from "@inertiajs/react";
import { cn } from "@/utils";

export default function Pricing() {
    const { auth, billing } = usePage().props;
    const [billingInterval, setBillingInterval] = useState(billing.periods[0]);
    const tiers = billing.tiers;

    // Group tiers by name and billing period
    const groupedTiers = useMemo(() => {
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
                currency: tier.currency || "usd",
                popular: tier.popular,
            };
        });

        return result;
    }, [tiers]);

    // Convert to array and sort by price (lowest first)
    const tiersArray = useMemo(() => {
        return Object.values(groupedTiers).sort((a, b) => {
            const aPrice = a.periods[billingInterval]?.price || 0;
            const bPrice = b.periods[billingInterval]?.price || 0;
            return aPrice - bPrice;
        });
    }, [groupedTiers, billingInterval]);

    return (
        <div className="bg-gradient-to-b from-gray-900 to-black py-20 rounded-lg">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl sm:text-center">
                    <span className="inline-flex items-center rounded-md bg-indigo-500/10 px-3 py-1 text-sm font-medium text-indigo-400 ring-1 ring-inset ring-indigo-500/20 mb-4">
                        Subscription Plans
                    </span>
                    <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">
                        Choose your plan
                    </h2>
                    <p className="mt-6 text-lg leading-8 text-gray-300">
                        Select the perfect plan for your needs with flexible
                        billing options.
                    </p>
                </div>

                <div className="mt-10 flex justify-center">
                    <div className="relative flex rounded-full bg-gray-800/40 p-1.5 backdrop-blur-sm shadow-lg border border-gray-700/50">
                        <button
                            type="button"
                            className={`${billingInterval === "monthly" ? "bg-gradient-to-r from-indigo-600 to-indigo-500 shadow-md shadow-indigo-500/20" : "hover:bg-gray-700/50 text-gray-300"}
                                rounded-full py-2.5 px-8 text-sm font-medium text-white focus:outline-none transition-all duration-200 ease-in-out`}
                            onClick={() => setBillingInterval("monthly")}
                        >
                            Monthly
                        </button>
                        <button
                            type="button"
                            className={`${billingInterval === "yearly" ? "bg-gradient-to-r from-indigo-600 to-indigo-500 shadow-md shadow-indigo-500/20" : "hover:bg-gray-700/50 text-gray-300"}
                                ml-1 rounded-full py-2.5 px-8 text-sm font-medium text-white focus:outline-none transition-all duration-200 ease-in-out`}
                            onClick={() => setBillingInterval("yearly")}
                        >
                            Yearly
                            {/* you can add other touches like this */}
                            {/* <span className="ml-1.5 inline-flex items-center rounded-full bg-indigo-800 px-2 py-0.5 text-xs font-medium text-white">
                                Save 20%
                            </span> */}
                        </button>
                    </div>
                </div>

                {/* Pricing cards */}
                <div
                    className={`mx-auto mt-16 grid max-w-md grid-cols-1 gap-10 lg:max-w-7xl ${tiersArray.length === 2 ? "lg:grid-cols-2" : "lg:grid-cols-3"}`}
                >
                    {tiersArray.map((tier, index) => {
                        const tierData = tier.periods[billingInterval] || {};
                        const isHighlighted = tierData.popular;

                        return (
                            <div className="h-full" key={tier.name}>
                                <div
                                    className={`relative group h-full ${isHighlighted ? "z-10 scale-105" : ""}`}
                                >
                                    <div
                                        className={`absolute -inset-px bg-gradient-to-r ${isHighlighted ? "from-purple-600 to-indigo-600 opacity-60" : "from-indigo-500 to-purple-600 opacity-0"} group-hover:opacity-${isHighlighted ? "90" : "80"} rounded-3xl blur-[6px] transition duration-300 group-hover:duration-200`}
                                    ></div>
                                    <div
                                        className={`relative bg-gradient-to-b from-gray-800/80 to-gray-900/90 p-7 py-10 rounded-3xl border ${isHighlighted ? "border-indigo-500/50" : "border-gray-700/50"} shadow-xl backdrop-blur-sm h-full`}
                                    >
                                        {isHighlighted && (
                                            <div className="absolute -top-4 left-0 right-0 flex justify-center">
                                                <span className="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-1 text-xs font-semibold uppercase tracking-wider text-white rounded-full shadow-md shadow-indigo-500/20">
                                                    Most Popular
                                                </span>
                                            </div>
                                        )}

                                        <div className="flex items-center justify-between mb-6">
                                            <div>
                                                <h2 className="text-xl font-bold text-white">
                                                    {tier.name}
                                                </h2>
                                                <p className="text-sm text-indigo-200/70 mt-1">
                                                    {tier.description}
                                                </p>
                                            </div>
                                            <div
                                                className={`h-10 w-10 rounded-full ${isHighlighted ? "bg-indigo-500/20" : "bg-indigo-500/10"} flex items-center justify-center`}
                                            >
                                                {/* you can add icons like this. We've set up some for popular tier names */}
                                                {tier.name === "Pro" ||
                                                tier.name === "professional" ? (
                                                    <svg
                                                        className="h-5 w-5 text-indigo-400"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                                        />
                                                    </svg>
                                                ) : tier.name === "Starter" ||
                                                  tier.name === "Basic" ? (
                                                    <svg
                                                        className="h-5 w-5 text-indigo-400"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M13 10V3L4 14h7v7l9-11h-7z"
                                                        />
                                                    </svg>
                                                ) : (
                                                    <svg
                                                        className="h-5 w-5 text-indigo-400"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                            strokeWidth={2}
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                        />
                                                    </svg>
                                                )}
                                            </div>
                                        </div>

                                        <div
                                            className={`relative flex flex-col bg-black/30 p-6 rounded-2xl border ${isHighlighted ? "border-indigo-500/30" : "border-gray-800/50"} shadow-inner`}
                                        >
                                            <div className="flex justify-center items-center mb-6">
                                                <span className="text-5xl font-bold text-white">
                                                    {/* you can update the minimumFractionDigits to display in the format 10.00 */}
                                                    {new Intl.NumberFormat(
                                                        "en-US",
                                                        {
                                                            style: "currency",
                                                            currency:
                                                                tierData.currency,
                                                            minimumFractionDigits: 0,
                                                        },
                                                    ).format(
                                                        tierData.price || 0,
                                                    )}
                                                </span>
                                                <span className="ml-2 text-sm text-indigo-200/70">
                                                    <span className="font-medium text-indigo-300">
                                                        per{" "}
                                                        {billingInterval.replace(
                                                            "ly",
                                                            "",
                                                        )}
                                                    </span>
                                                    <br />
                                                    <span>
                                                        plus local taxes
                                                    </span>
                                                </span>
                                            </div>

                                            <a
                                                href={tierData.link || "#"}
                                                className={cn(
                                                    "group/button relative overflow-hidden rounded-lg",
                                                    isHighlighted
                                                        ? "bg-gradient-to-r from-indigo-500 to-purple-600"
                                                        : "bg-gradient-to-r from-indigo-500 to-indigo-600",
                                                    "px-4 py-3 shadow-md transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/25 text-center",
                                                    auth.user.subscription
                                                        ?.plan_id ===
                                                        tierData.id
                                                        ? "cursor-not-allowed"
                                                        : "cursor-pointer",
                                                )}
                                                onClick={(e) => {
                                                    if (
                                                        auth.user.subscription
                                                            ?.plan_id ===
                                                        tierData.id
                                                    ) {
                                                        e.preventDefault();
                                                    }
                                                }}
                                            >
                                                <div className="relative z-10 flex items-center justify-center text-sm font-semibold text-white">
                                                    {auth.user.subscription
                                                        ?.plan_id ===
                                                    tierData.id ? (
                                                        <span className="text-white">
                                                            Current Plan
                                                        </span>
                                                    ) : (
                                                        <>
                                                            {auth.user
                                                                .hasSubscription
                                                                ? "Switch to plan"
                                                                : "Subscribe now"}
                                                            <svg
                                                                className="ml-2 h-4 w-4 transition-transform duration-300 group-hover/button:translate-x-1"
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    strokeWidth={
                                                                        2
                                                                    }
                                                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                                                />
                                                            </svg>
                                                        </>
                                                    )}
                                                </div>
                                                <div
                                                    className={`absolute inset-0 z-0 ${isHighlighted ? "bg-gradient-to-r from-purple-600 to-indigo-600" : "bg-gradient-to-r from-indigo-600 to-indigo-500"} opacity-0 transition-opacity duration-300 group-hover/button:opacity-100`}
                                                ></div>
                                            </a>

                                            <p className="mt-4 flex justify-center text-sm text-indigo-200/60 sm:space-x-2">
                                                <span>
                                                    {tierData.trialDays &&
                                                    !auth.user.hasSubscription
                                                        ? `${tierData.trialDays} days free trial`
                                                        : "Cancel anytime. No setup fees."}
                                                </span>
                                            </p>
                                        </div>

                                        <h3 className="sr-only">
                                            {tier.name} plan features
                                        </h3>

                                        <ul className="mt-8 space-y-5 text-sm text-indigo-200/70">
                                            {tier.features &&
                                                tier.features.map(
                                                    (feature, featureIndex) => (
                                                        <li
                                                            className="flex gap-3"
                                                            key={featureIndex}
                                                        >
                                                            <div
                                                                className={`flex-shrink-0 flex h-6 w-6 items-center justify-center rounded-full ${isHighlighted ? "bg-indigo-500/20" : "bg-indigo-500/10"} text-indigo-400`}
                                                            >
                                                                <svg
                                                                    className="h-4 w-4"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        strokeLinecap="round"
                                                                        strokeLinejoin="round"
                                                                        strokeWidth={
                                                                            2
                                                                        }
                                                                        d="M5 13l4 4L19 7"
                                                                    />
                                                                </svg>
                                                            </div>
                                                            <p>
                                                                <strong className="font-semibold text-white">
                                                                    {feature.title ??
                                                                        feature.description}
                                                                </strong>
                                                                {feature?.title &&
                                                                    feature?.description && (
                                                                        <span className="block text-indigo-200/60 mt-1">
                                                                            {
                                                                                feature.description
                                                                            }
                                                                        </span>
                                                                    )}
                                                            </p>
                                                        </li>
                                                    ),
                                                )}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
