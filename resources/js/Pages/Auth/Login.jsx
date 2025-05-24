import { useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
  Card,
  CardBody,
  Input, // Mengganti input HTML dengan Input dari Material Tailwind
  Checkbox, // Mengganti checkbox HTML dengan Checkbox dari Material Tailwind
  Button,
  Typography,
} from "@material-tailwind/react";
import { LockClosedIcon, AtSymbolIcon, ArrowRightIcon } from '@heroicons/react/24/solid'; // Contoh ikon

// Anda bisa menggunakan gambar yang sama atau yang lain
const ImgSignIn = '/images/stmik.png'; // Pastikan gambar ini ada di public/images

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
            <section className="min-h-screen flex items-center justify-center bg-blue-gray-50 p-4"> {/* Warna latar dari Material Tailwind */}
                <div className="container mx-auto flex flex-col md:flex-row items-center justify-center gap-8 lg:gap-16">
                    {/* Bagian Gambar (Kiri) */}
                    <div className="hidden md:flex w-full md:w-1/2 lg:w-2/5 justify-center">
                        <img
                            src={ImgSignIn}
                            alt="STMIK Bandung"
                            className="w-auto h-auto max-w-sm lg:max-w-md object-contain"
                        />
                    </div>

                    {/* Bagian Form (Kanan) */}
                    <div className="w-full md:w-1/2 lg:w-2/5">
                        <Card className="w-full max-w-md mx-auto shadow-2xl border border-blue-gray-100">
                            <CardBody className="p-6 sm:p-8">
                                <Typography variant="h4" color="blue-gray" className="text-center mb-1">
                                    Sign In
                                </Typography>
                                <Typography color="gray" className="text-center font-normal mb-6">
                                    Masuk ke akun Anda untuk melanjutkan.
                                </Typography>

                                {status && (
                                    <div className="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                                        {status}
                                    </div>
                                )}

                                <Button
                                    variant="outlined"
                                    color="blue-gray"
                                    className="w-full flex items-center justify-center gap-3 mb-4"
                                    // onClick={handleGoogleSignIn} // Tambahkan fungsi jika ada
                                >
                                    <svg className="w-4 h-4" aria-hidden="true" focusable="false" data-prefix="fab" data-icon="google" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 488 512">
                                        <path fill="currentColor" d="M488 261.8C488 403.3 381.5 504 248 504 110.8 504 0 393.2 0 256S110.8 8 248 8c66.8 0 123 24.5 166.3 64.9l-67.5 64.9C258.5 52.6 94.3 116.6 94.3 256c0 86.5 69.1 156.6 153.7 156.6 98.2 0 135-70.4 140.8-106.9H248v-85.3h236.1c2.3 12.7 3.9 24.9 3.9 41.4z"></path>
                                    </svg>
                                    Sign In with Google
                                </Button>

                                <div className="flex items-center my-6">
                                    <hr className="flex-grow border-blue-gray-100" />
                                    <Typography as="span" variant="small" color="blue-gray" className="px-3 opacity-75">
                                        ATAU
                                    </Typography>
                                    <hr className="flex-grow border-blue-gray-100" />
                                </div>


                                <form className="space-y-5" onSubmit={submit}>
                                    <div>
                                        <Input
                                            type="email"
                                            color="blue" // Atau warna tema Anda
                                            label="Email"
                                            name="email"
                                            id="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            error={errors.email ? true : false}
                                            icon={<AtSymbolIcon className="h-5 w-5 text-blue-gray-300"/>}
                                            required
                                            autoFocus
                                        />
                                        {errors.email && (
                                            <Typography variant="small" color="red" className="mt-1 flex items-center font-normal">
                                                {errors.email}
                                            </Typography>
                                        )}
                                    </div>
                                    <div>
                                        <Input
                                            type="password"
                                            color="blue"
                                            label="Password"
                                            name="password"
                                            id="password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            error={errors.password ? true : false}
                                            icon={<LockClosedIcon className="h-5 w-5 text-blue-gray-300"/>}
                                            required
                                        />
                                         {errors.password && (
                                            <Typography variant="small" color="red" className="mt-1 flex items-center font-normal">
                                                {errors.password}
                                            </Typography>
                                        )}
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <Checkbox
                                            id="remember"
                                            name="remember"
                                            label={<Typography variant="small" color="gray" className="font-normal">Remember me</Typography>}
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                            color="blue"
                                        />
                                        {canResetPassword && (
                                            <Typography
                                                as={Link}
                                                href={route('password.request')}
                                                variant="small"
                                                color="blue" // Warna link dari Material Tailwind
                                                className="font-medium hover:underline"
                                            >
                                                Lupa password?
                                            </Typography>
                                        )}
                                    </div>
                                    <Button
                                        type="submit"
                                        color="blue" // Warna tombol utama
                                        fullWidth
                                        disabled={processing}
                                        className="flex items-center justify-center gap-2"
                                    >
                                        <ArrowRightIcon className="h-5 w-5"/>
                                        Sign In
                                    </Button>
                                    {/* <Typography variant="small" color="gray" className="mt-4 text-center font-normal">
                                        Belum punya akun?{' '}
                                        <Typography
                                            as={Link}
                                            href={route('register')}
                                            variant="small"
                                            color="blue" // Warna link dari Material Tailwind
                                            className="font-medium hover:underline"
                                        >
                                            Sign up
                                        </Typography>
                                    </Typography> */}
                                </form>
                            </CardBody>
                        </Card>
                    </div>
                </div>
            </section>
        </>
    );
}