import { useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/Components/InputError';

const ImgSignIn = '/images/stmik.png';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <>
            <Head title="Sign In" />
            <section className="bg-gray-50"> {/* dark:bg-gray-900 dihapus */}
                <div className="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0">
                    {/* Judul STMIK Bandung (jika ingin ditampilkan) */}
                    {/* <div className="mb-6 text-center w-full">
                        <h2 className="text-4xl font-[merriweather] font-bold mt-3 text-gray-900"> STMIK BANDUNG </h2>
                    </div> */}

                    <div className="flex w-full max-w-6xl items-center">
                        <div className="hidden md:flex w-1/2 justify-center items-center pr-8 lg:pr-16">
                            <img
                                src={ImgSignIn}
                                alt="STMIK Bandung"
                                className="w-auto h-auto max-w-md lg:max-w-lg"
                            />
                        </div>

                        <div className="w-full md:w-1/2 bg-white rounded-lg shadow border border-gray-200 md:mt-0 sm:max-w-md xl:p-0 mx-auto"> {/* dark classes dihapus, ditambah border standar */}
                            <div className="p-6 space-y-4 md:space-y-6 sm:p-8">
                                <h1 className="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl text-center">
                                    Sign in to your account
                                </h1>

                                {status && <div className="mb-4 font-medium text-sm text-green-600">{status}</div>}

                                <div className="w-full mt-6">
                                    <button type="button" className="w-full text-gray-900 bg-white hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center justify-center">
                                        <div className="mr-2">
                                            <svg className="w-4 h-4" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="google" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512">
                                                <path fill="currentColor" d="M488 261.8C488 403.3 381.5 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"></path>
                                            </svg>
                                        </div>
                                        Sign In with Google
                                    </button>
                                </div>

                                <div className="flex items-center my-6">
                                    <hr className="flex-grow border-gray-300" />
                                    <span className="px-4 text-sm text-gray-500">Or</span>
                                    <hr className="flex-grow border-gray-300" />
                                </div>

                                <form className="space-y-4 md:space-y-6" onSubmit={submit}>
                                    <div>
                                        <label htmlFor="email" className="block mb-2 text-sm font-medium text-gray-900 text-left">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            id="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            className="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 placeholder-gray-400"
                                            placeholder="name@gmail.com"

                                        />
                                        <InputError message={errors.email} className="mt-2 text-xs text-red-600" />
                                    </div>
                                    <div>
                                        <label htmlFor="password" className="block mb-2 text-sm font-medium text-gray-900 text-left">Password</label>
                                        <input
                                            type="password"
                                            name="password"
                                            id="password"
                                            placeholder="••••••••"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            className="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5 placeholder-gray-400"

                                        />
                                        <InputError message={errors.password} className="mt-2 text-xs text-red-600" />
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-start">
                                            <div className="flex items-center h-5">
                                                <input
                                                    id="remember"
                                                    aria-describedby="remember"
                                                    type="checkbox"
                                                    name="remember"
                                                    checked={data.remember}
                                                    onChange={(e) => setData('remember', e.target.checked)}
                                                    className="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 accent-primary-600"
                                                />
                                            </div>
                                            <div className="ml-3 text-sm">
                                                <label htmlFor="remember" className="text-gray-500">Remember me</label>
                                            </div>
                                        </div>
                                        {canResetPassword && (
                                            <Link
                                                href={route('password.request')}
                                                className="text-sm font-medium text-blue-600 hover:underline"
                                            >
                                                Forgot password?
                                            </Link>
                                        )}
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center disabled:opacity-50"
                                    >
                                        Sign in
                                    </button>
                                    <p className="text-sm font-light text-gray-500">
                                        Don’t have an account yet?{' '}
                                        <Link href={route('register')} className="font-medium text-blue-600 hover:underline"> {/* Konsistenkan juga warna link ini */}
                                            Sign up
                                        </Link>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </>
    );
}