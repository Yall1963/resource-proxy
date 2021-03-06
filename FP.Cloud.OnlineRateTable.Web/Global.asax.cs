﻿using System;
using System.Collections.Generic;
using System.Globalization;
using System.Linq;
using System.Security.Claims;
using System.Web;
using System.Web.Helpers;
using System.Web.Mvc;
using System.Web.Optimization;
using System.Web.Routing;

namespace FP.Cloud.OnlineRateTable.Web
{
    public class MvcApplication : System.Web.HttpApplication
    {
        protected void Application_Start()
        {
            AreaRegistration.RegisterAllAreas();
            FilterConfig.RegisterGlobalFilters(GlobalFilters.Filters);
            RouteConfig.RegisterRoutes(RouteTable.Routes);
            BundleConfig.RegisterBundles(BundleTable.Bundles);

            AntiForgeryConfig.UniqueClaimTypeIdentifier = ClaimTypes.NameIdentifier;
        }

        private void SetCulture()
        {
            string culture = (string)Session["Language"];
            if(string.IsNullOrEmpty(culture) == false)
            {
                CultureInfo info = new CultureInfo(culture);
                System.Threading.Thread.CurrentThread.CurrentCulture = info;
                System.Threading.Thread.CurrentThread.CurrentUICulture = info;
            }
        }
    }
}
