import { Link } from "react-router-dom";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import * as z from "zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/ui/form";

const userSchema = z.object({
  name: z.string().trim().min(1, "Meno je povinné").max(100),
  email: z.string().trim().email("Neplatná emailová adresa").max(255),
  password: z.string().min(8, "Heslo musí mať aspoň 8 znakov").max(100),
  confirmPassword: z.string(),
}).refine((data) => data.password === data.confirmPassword, {
  message: "Heslá sa nezhodujú",
  path: ["confirmPassword"],
});

export type UserFormData = z.infer<typeof userSchema>;

interface UserRegistrationStepProps {
  onSubmit: (data: UserFormData) => void;
}

export const UserRegistrationStep = ({ onSubmit }: UserRegistrationStepProps) => {
  const userForm = useForm<UserFormData>({
    resolver: zodResolver(userSchema),
    defaultValues: {
      name: "",
      email: "",
      password: "",
      confirmPassword: "",
    },
  });

  return (
    <>
      <CardHeader className="space-y-1">
        <CardTitle className="text-3xl font-bold">Registrácia</CardTitle>
        <CardDescription className="text-base">
          Vytvorte si nový účet
        </CardDescription>
      </CardHeader>
      <CardContent>
        <Form {...userForm}>
          <form onSubmit={userForm.handleSubmit(onSubmit)} className="space-y-6">
            <div className="space-y-4">
              <FormField
                control={userForm.control}
                name="name"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Meno *</FormLabel>
                    <FormControl>
                      <Input placeholder="Vaše meno" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <FormField
                control={userForm.control}
                name="email"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel>Email *</FormLabel>
                    <FormControl>
                      <Input type="email" placeholder="vas@email.sk" {...field} />
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <FormField
                  control={userForm.control}
                  name="password"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Heslo *</FormLabel>
                      <FormControl>
                        <Input type="password" placeholder="••••••••" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={userForm.control}
                  name="confirmPassword"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel>Potvrďte heslo *</FormLabel>
                      <FormControl>
                        <Input type="password" placeholder="••••••••" {...field} />
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>
            </div>

            <Button type="submit" className="w-full" size="lg">
              Pokračovať
            </Button>

            <div className="text-center text-sm text-muted-foreground">
              Už máte účet?{" "}
              <Link to="/login" className="text-primary hover:underline font-medium">
                Prihláste sa
              </Link>
            </div>
          </form>
        </Form>
      </CardContent>
    </>
  );
};
